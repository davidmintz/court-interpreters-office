<?php
/** module/Admin/src/Service/ScheduleUpdateManager.php  */

namespace InterpretersOffice\Admin\Service;

use Zend\EventManager\EventInterface;
use Zend\Log\LoggerInterface;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Filter\Word\DashToCamelCase;
use Zend\View\Renderer\RendererInterface as Renderer;
use Zend\View\Model\ViewModel;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\EntityManagerInterface;

use InterpretersOffice\Entity;
use InterpretersOffice\Requests\Entity\Request;
use InterpretersOffice\Requests\Entity\Listener\RequestEntityListener;
use InterpretersOffice\Service\EmailTrait;

/**
 * Reacts to changes in Request entities.
 *
 * May also eventually be pressed into service for doing things in response to
 * changes in Event entities.
 */
class ScheduleUpdateManager
{
    use EmailTrait;

    /**
     * @var string
     */
    const CHANGE_DATE = 'change-date';

    /**
     * @var string
     */
    const CHANGE_TIME_X_PM = 'change-x-am-pm';

    /**
     * @var string
     */
    const CHANGE_TIME_WITHIN_AM_PM = 'change-within-am-pm';

    /**
     * @var string
     */
    const CHANGE_LANGUAGE = 'change-language';

    /**
     * @var string
     */
    const CANCEL = 'cancel';

    /**
     * @var string
     */
    const OTHER = 'other';

    /**
     * @var string
     */
    const ACTION_REMOVE_INTERPRETERS = 'remove-interpreters';

    /**
     * @var string
     */
    const ACTION_NOTIFY_INTERPRETERS = 'notify-assigned-interpreters';

    /**
     * @var string
     */
    const ACTION_DELETE_SCHEDULED_EVENT = 'delete-scheduled-event';


    /**
     * LoggerInterface
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * auth
     * @var AuthenticationServiceInterface
     */
    private $auth;

    /**
     * configuration
     *
     * @var Array
     */
    protected $config;

    /**
     * pre-update state of Request entity
     *
     * @var array
     */
    protected $previous_state;

    /**
     * interpreters assigned to related Event entity
     *
     * @var array
     */
    private $interpreters = [];

    /**
     * entity manager
     *
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $objectManager;

    /**
     * the (most significant) user event/action
     * @var string
     */
    private $user_event;

    /**
     * viewRenderer
     *
     * @var Renderer
     */
    private $viewRenderer;

    /**
     * Container for email messages.
     *
     * Email messages are kept here until the database transaction
     * completes successfully.
     *
     * @var array
     */
    private $email_messages = [];

    /**
     * whether interpreters should be deleted from the Event
     * @var boolean
     */
    private $remove_interpreters;

    /**
     * whether interpreters should be sent email notification
     * @var boolean
     */
    private $notify_interpreters;

    /**
     * constructor
     *
     * @param AuthenticationServiceInterface $auth
     * @param Array                          $config
     * @param LoggerInterface                $log
     */
    public function __construct(

        AuthenticationServiceInterface $auth,
        Array $config,
        LoggerInterface $log
    ) {
        $this->logger = $log;
        $this->config = $config;
        $this->auth = $auth;
    }

    /**
     * saves copy of Request for comparison after update
     *
     * @param Request $request
     * @return ScheduleUpdateManager
     */
    public function setPreviousState(Request $request)
    {
        $this->previous_state = [
            'date' =>  $request->getDate(),
            'time' =>  $request->getTime(),
            'language' => (string)$request->getLanguage(),
            'event_type' =>  (string)$request->getEventType(),
            'location' =>  (string)$request->getLocation(),
            'docket'  => $request->getDocket(),
            'comments' => $request->getComments(),
            'judge' =>  (string)$request->getJudge(),
            'extraData' => $request->getExtraData(),
            'defendants' => $request->getDefendants()->toArray(),
            //'is_default_location' => 'whatever',
        ];

        return $this;
    }


    /**
     * sets entity previous state
     *
     * @param Array $data
     * @return ScheduleUpdateManager
     */
    public function setPreviousEventState(Array $data) {
        $this->previous_state = $data;

        return $this;
    }

    /**
     * sets viewRenderer
     *
     * @param Renderer $viewRenderer
     * @return ScheduleUpdateManager
     */
    public function setViewRenderer(Renderer $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer;

        return $this;
    }

    /**
     * sets auth instance
     *
     * primarily for rigging tests
     *
     * @param AuthenticationServiceInterface $auth
     * @return ScheduleUpdateManager;
     */
    public function setAuth(AuthenticationServiceInterface $auth)
    {
        $this->auth = $auth;

        return $this;
    }

    /**
     * runs the configured actions
     *
     * @param  Request $request
     * @param  string  $user_event
     * @param  array $updates
     * @return ScheduleUpdateManager
     */
    public function executeActions(Request $request, $user_event, Array $updates)
    {
        // figure out what admin actions are configured for $user_event
        $actions = $this->getUserActions($request,$user_event);
        $config = $this->config['event_listeners'];
        $this->logger->debug("configured actions for user_event $user_event: ".print_r( $config[$user_event],true));
        // $this->logger->debug("configured user actions for $user_event: ".print_r($actions,true));
        $filter = new DashToCamelCase();
        // order of execution might not be guaranteed, so we need to
        // watch and take note if "remove-interpreters" is required
        // before running into notify-interpreters, so we will know
        // what template to use for email
        //
        $pattern = sprintf('/%s|%s/',self::ACTION_REMOVE_INTERPRETERS, self::ACTION_DELETE_SCHEDULED_EVENT);
        $shit = preg_grep($pattern, $actions);
        $this->logger->debug('\$shit at '.__LINE__.  " is: ".print_r($shit,true));
        if ($shit) {
            $this->remove_interpreters = $config[$user_event][array_values($shit)[0]]  ;
        }
        $pattern = sprintf('/%s/',self::ACTION_NOTIFY_INTERPRETERS);
        $shit = preg_grep($pattern, $actions);
        if ($shit) {
            $this->notify_interpreters = $config[$user_event][array_values($shit)[0]];
        }
        $this->logger->debug("user-event $user_event: we have set remove_interpreters = "
            .($this->remove_interpreters ? "true":"false"));
        $this->logger->debug("user-event $user_event: we have set notify_interpreters = "
                .($this->notify_interpreters ? "true":"false"));
        foreach ($actions as $string) {
            $i = strrpos($string, '.') + 1;
            $action = substr($string, $i);
            $method = lcfirst($filter->filter($action));
            if ($config[$user_event][$string]) {
                $this->logger->debug(__FUNCTION__.":  examining action '$action'");
                if (method_exists($this, $method)) {
                    $this->logger->debug("we now need to call: $method()");
                    $this->$method($request, $updates);
                } else {
                    $this->logger->warn("!! not running not-implemented method: $method !");
                }
            } else {
                // action is explicity disabled in configuration
                $this->logger->info("NOT doing disabled $action in response to user-event: $user_event",
                    [
                        'entity_class'=>Request::class,
                        'entity_id' => $request->getId(),
                        'channel' => 'schedule-update-manager',
                    ]
                );
                if ('notify-assigned-interpreters' == $action) {
                    $event = $request->getEvent();
                    $interpreters = $this->interpreters ?: $event->getInterpreters();
                    $this->logger->debug("we need to send a heads-up to interpreters office");
                    $user = $this->auth->getIdentity();
                    $view = new ViewModel(compact('request','user_event',
                        'updates','interpreters','user'));
                    $view->before = $this->previous_state;
                    $view->setTemplate('email/autocancellation-notice');
                    $layout = new ViewModel();
                    $layout->setTemplate('interpreters-office/email/layout')
                        ->setVariable('content', $this->viewRenderer->render($view));
                    $output = $this->viewRenderer->render($layout);
                    // debug
                    file_put_contents('data/email-autocancellation.html',$output);
                    $message = $this->createEmailMessage($output);
                    $contact = $this->config['site']['contact'];

                    $message->setTo($contact['email'],$contact['organization_name'])
                        ->setFrom($contact['email'],$contact['organization_name'])
                        ->setSubject($event->describe());
                    $this->email_messages[] = $message;
                }
            }
        }

        return $this;
    }

    /**
     * Sends email messages created earlier in request cycle.
     *
     * Email message objects are saved in $this->email_messages so we can wait
     * until the database transaction completes successfully, rather than risk
     * sending email that announced something that didn't happen.
     *
     * @param  EventInterface $e [description]
     * @return void
     */
    public function dispatchEmail(EventInterface $e)
    {
        $this->logger->debug(sprintf("now going to DISPATCH %d emails",count($this->email_messages)));
        if ($this->email_messages) {
            try {
                $transport = $this->getMailTransport();
                foreach ($this->email_messages as $message) {
                    $message->getHeaders()->addHeaderLine('X-Generated-By', 'InterpretersOffice https://interpretersoffice.org');
                    $transport->send($message);
                    $this->logger->debug("sent email to someone");
                }
            } catch (\Exception $e) {
                $this->logger->err(
                    "shit! ScheduleUpdateManager exception while sending email: "
                    .$e->getMessage()
                );
                throw $e;
            }
        }
    }

    /**
     * event listener for Request update
     *
     * @param  Event  $e
     * @return void
     */
    public function onUpdateRequest(EventInterface $e)
    {
        $this->logger->debug(__METHOD__.": here's Johnny!");
        $request = $e->getParam('request');
        $event = $request->getEvent();
        $em =  $e->getParam('entity_manager');
        $this->objectManager = $em;
        $previous = $this->previous_state;
        $updates = [];
        foreach ($previous as $field => $value) {
            if ($field == 'is_default_location') {
                continue;
            }
            if ($field == 'event_type') {
                $after = $request->getEventType();
            } else {
                $after = $request->{'get'.ucfirst($field)}();
            }
            if ($field == 'defendants') {
                $after = $after->toArray();
            }
            if ($previous[$field] != $after) {
                $updates[$field] = [$previous[$field],$after];
            }
        }
        $what = implode(', ',array_keys($updates));
        $message = sprintf(
            'user %s updated request #%d (%s)',
            $this->auth->getIdentity()->username,
            $request->getId(),
            $what
        );
        $this->logger->info($message,[
            'entity_class'=>Request::class,
            'entity_id' => $request->getId(),
            'channel'  => 'requests',
        ]);
        if (! $event) {
            $this->logger->debug(
                __FUNCTION__
                .": request apparently NOT scheduled, no event to update"
            );
            return;
        }
        $user_event = $this->getUserEvent($updates);
        $this->user_event = $user_event;
        $this->logger->debug(
             sprintf(__METHOD__.":\nuser-event is '%s'  at %d",
             $user_event, __LINE__)
         );
        if (! isset($this->config['event_listeners'][$user_event])) {
            $this->logger->debug(__METHOD__.
            ":\nno configuration found for user-event '$user_event'
                TO DO: IMPLEMENT");

            return $this->updateScheduledEvent($request,$updates);
        }
        $this->executeActions($request, $user_event, $updates);

        if ($user_event == self::OTHER) {
            $this->logger->debug(__METHOD__.
                ":  it's the default user-action: other");
            $this->updateScheduledEvent($request,$updates);
        }

        return $this;
    }

    /**
     * figures out what admin actions (methods) apply based on $user_event
     *
     * @param  Request $request
     * @param  string  $user_event
     * @return Array of strings/methods to be run
     */
    protected function getUserActions(Request $request, $user_event)
    {
        $config = $this->config['event_listeners'];
        $type = (string)$request->getEventType()->getCategory()
            == 'in' ? 'in-court' : 'out-of-court';
        $language = (string) $request->getEvent()->getLanguage() == 'Spanish' ?
             'spanish' : 'non-spanish';
        $pattern = "/^(all-events|$type)\.(all-languages|$language)\./";

        return preg_grep($pattern, array_keys($config[$user_event]));
    }


    /**
     * event listener for Request creation
     *
     * @param  Event  $e
     * @return void
     */
    public function onCreateRequest(EventInterface $e)
    {
        $this->logger->debug(
            sprintf('observing request-create in %s at %d', __METHOD__, __LINE__)
        );
        $listener_config = $this->config['event_listeners'];
        // to be continued?
    }

    /**
     * listener for request-cancellation event
     *
     * @param  EventInterface $e
     * @return void
     */
    public function onCancelRequest(EventInterface $e)
    {
        $this->logger->debug(
            sprintf('handling request cancel in %s at %d', __METHOD__, __LINE__)
        );
        $this->user_event = 'cancel';
        $request = $e->getParam('request');
        $this->objectManager = $e->getParam('entity_manager');
        $event = $request->getEvent();
        $this->logger->debug(
            sprintf('is there an event? in %s at %d? %s', __METHOD__, __LINE__,
            $event === null ? "NO":"YES"
        )
        );
        if ($event) {
            $this->executeActions($request, self::CANCEL,['cancelled'=>[0,1]]);
        }
        $message = sprintf('user %s cancelled a request (id %d) for %s on %s',
            $this->auth->getIdentity()->username,
            $request->getId(),
            $request->getLanguage(),
            $request->getDate()->format('d-M-Y')
        );
        $this->logger->info($message,
            ['entity_class' => Request::class,'entity_id' => $request->getId(),'channel'=>'schedule-update-manager']
        );
    }

    public function onDeleteEvent(EventInterface $e) {
        $params = $e->getParams();
        if (empty($params['email_notification'])) {
            return;
        }
        /* otherwise... */
        $this->remove_interpreters = true;
        $this->notify_interpreters = true;// superfluous?
        $this->user_event = 'delete';
        $event = $params['entity'];
        $this->logger->debug(
            __METHOD__. " calling notifyAssignedInterpreters()"
        );
        $this->notifyAssignedInterpreters($event);


    }

    /**
     * updates an Event entity to synchronize with Request
     *
     * @param  Request $request
     * @param Array $updates
     * @return ScheduleUpdateManager
     *
     */
    public function updateScheduledEvent(Request $request,Array $updates)
    {
        $event = $request->getEvent();
        if (! $event) {
            $this->logger->debug(__METHOD__.": no scheduled event, nothing to do");
            return; // important! from here on we might just assume...
        }
        $props = [
            'date','time','judge','language','eventType','docket','location'
        ];
        $updatable = array_intersect($props, array_keys($updates));
        $shit = print_r(array_keys($updates), true);
        $this->logger->debug("what changed:\n$shit");
        if ($updatable) {
            foreach ($updatable as $prop) {
                $this->logger->debug(__METHOD__.": setting $prop on event entity");
                // PHP methods and functions may be case-insensitive, but
                // the gods will appreciate our workmanship and attention to
                // aesthetic detail
                $setter = 'set'.ucfirst($prop);
                $getter = 'get'.ucfirst($prop);
                $event->$setter($request->$getter());
            }
        }

        if (isset($updates['defendants'])) {
            $this->logger->debug("they updated the defendants. need to get busy");
            // were they the same before the Request update?
            $ours = $event->getDefendants()->toArray();
            $was_the_same = $ours = $updates['defendants'][0];
            $this->logger->debug("were they the same before? ".($was_the_same?"YES":"NO"));

            if ($was_the_same) {
                $updatable[] = 'defendants';
                 $theirs = $request->getDefendants()->toArray();
                 $to_be_added = array_diff($theirs,$ours);
                 foreach ($to_be_added as $d) {
                      $this->logger->debug("need to add? ".$d->getSurnames());
                      $event->addDefendant($d);
                 }
                 $to_be_removed = array_diff($ours,$theirs);
                 foreach ($to_be_removed as $d) {
                      $this->logger->debug("need to remove? ".$d->getSurnames());
                      $event->removeDefendant($d);
                 }
            } // else it's too complicated.
        }
        if ($updatable) {
            $this->logger->info(
                sprintf(
                    'event #%d was auto-updated to keep consistent with request #%s',
                    $event->getId(), $request->getId()
                ),['entity_class'=>Entity\Event::class,'entity_id'=>$event->getId(),'channel'=>'schedule-update-manager']
            );
        }
        // send FYI to your Interpreters Office.
        //** @todo DRY this out */
        $user = $this->auth->getIdentity();
        $view_variables = [
            'user' => $user,
            'entity' => $request,
            'before'=> $this->previous_state,
            'interpreters' => $event->getInterpreters(),
            'notify_interpreters' => $this->notify_interpreters,
            'remove_interpreters' => $this->remove_interpreters,
        ];
        $subject = sprintf('update: %s',$event->describe());
        $template = 'email/schedule-update-notice';
        $view = (new ViewModel())
            ->setTemplate($template)
            ->setVariables($view_variables);
        $layout = new ViewModel();
        $layout->setTemplate('interpreters-office/email/layout')
            ->setVariable('content', $this->viewRenderer->render($view));
        $output = $this->viewRenderer->render($layout);
        // debug
        $shit = basename($template);
        file_put_contents("data/$shit.html",$output);
        //$interpreters =
        $message = $this->createEmailMessage($output);
        $contact = $this->config['site']['contact'];
        $message->setTo($contact['email'],$contact['organization_name'])
            ->setFrom($contact['email'],$contact['organization_name'])
            ->setSubject($subject);

        $this->email_messages[] = $message;
        $this->logger->debug("queued up FYI email to office at ".__LINE__);
        return $this;
    }

    /**
     * deletes an Event entity to synchronize Requests with scheduled Events
     *
     * @param  Request $request
     * @param  array updates
     * @return ScheduleUpdateManager
     *
     */
    public function deleteScheduledEvent(Request $request, Array $updates)
    {
        $event = $request->getEvent();
        $event->setDeleted(true);
        //$this->user_event
        $this->logger->info(sprintf('event id %d has been automatically removed from the schedule',$event->getId()),
            ['entity_class'=> get_class($event),'entity_id'=>$event->getId(),'channel'=>'schedule-update-manager']);
        return $this;
    }

    /**
     * Un-assigns all interpreters from Request's corresponding Event
     *
     * Second parameter is not used, but is there to keep our signature
     * consistent with that of other methods (all are invoked dynamically).
     *
     * @param  Request $request
     * @param array $updates
     * @return ScheduleUpdateManager
     */
    public function removeInterpreters(Request $request, Array $updates)
    {
        $event = $request->getEvent();

        $interpreterEvents = $event->getInterpreterEvents();
        if ($interpreterEvents->count()) {
            $this->interpreters = $event->getInterpreters();
            $event->removeInterpreterEvents($interpreterEvents);
            $message = sprintf("removed interpreter(s) from event #%d: %s",
                $event->getId(),
                implode(", ",array_map(function($i){
                    return $i->getFullname();
                },$this->interpreters)));
            $this->logger->info($message,
                ['entity_class'=> get_class($event),'entity_id'=>$event->getId(),
                'channel'=>'schedule-update-manager','user-event'=>$this->user_event,'request_id'=>$request->getId()]
            );
        } else {
            $this->logger->debug(__METHOD__.": NO interpreters to remove ");
        }

        return $this;
    }

    /**
     * notifies interpreters about a change/cancellation
     *
     * note: as of now there is no guarantee which will be invoked first:
     * this method or removeInterpreters()
     *
     * @param  Request $request
     * @param  Array  $updates
     * @return ScheduleUpdateManager
     */
    public function notifyAssignedInterpreters(Entity\Interpretable $entity, Array $updates = [])
    {

        $interpreters = $this->interpreters ?: $entity->getInterpreters();
        $count = count($interpreters);
        if (! $count) {
            return $this;
        }
        $data = $this->previous_state;
        /* we need to determine the email template based on the action.
           if $this->remove_interpreters == true, tell them the event was cancelled.
           for the details, use the Request entity's previous_state
        */
        $subject = sprintf('%s interpreting assignment has been ',
        $data['language']);
        if ($this->remove_interpreters) {
            $subject .= 'cancelled';
            $view_variables = ['entity' => $data ];
            $template = 'email/interpreter-cancellation-notice';
        } else {
            $subject .= 'modified';
            $view_variables = ['entity' => $entity,'before'=> $this->previous_state ];
            $template = 'email/event-update-notice';
        }
        $subject .= sprintf(
            ' (%s, %s %s',
                isset($data['type'])? $data['type']:$data['event_type'],
                $data['date']->format('D d-M-Y'),
                $data['time']->format('g:i a')
        );
        if ($data['docket']) {
            $subject .= ", {$data['docket']}";
        }
        $subject .= ')';
        $view = (new ViewModel())
            ->setTemplate($template)
            ->setVariables($view_variables);
        $who = [];
        $layout = $this->getEmailLayout();

        foreach ($interpreters as $i) {
            $view->interpreter = $i;
            $who[] = $i->getFullName();
            $this->logger->debug("using $template to prepare email to: {$i->getEmail()}");
            $layout->setVariable('content', $this->viewRenderer->render($view));
            $output = $this->viewRenderer->render($layout);
            // for debugging
            $shit = basename($template);
            file_put_contents("data/$shit.html",$output);

            $message = $this->createEmailMessage($output);
            $contact = $this->config['site']['contact'];
            $this->setMessageHeaders($message,$i,$contact)
                ->setSubject($subject);
            $this->email_messages[] = $message;
        }
        $message = sprintf(
            'notifying interpreter(s) following user-action %s with %s #%s: %s',
            $this->user_event, get_class($entity), $entity->getId(), implode(', ',$who)
        );
        $this->logger->info($message,
            ['entity_class' => get_class($entity),
             'entity_id' => $entity->getId(),'channel'=>'email']);

        return $this;
    }

    /**
     * Determines which user-action on a Request is to be handled.
     *
     * When users update a request, user-configured event listeners are
     * triggered. They may update more than one field. We look at the changeSet
     * to determine which update is the most signicant, and return the
     * corresponding action name, which is mapped to a method.
     *
     * @param  Array $changeset
     * @return string name of the user action
     */
    private function getUserEvent(Array $changeset)
    {
        $fields = array_keys($changeset);
        if (in_array('language', $fields)) {
            return self::CHANGE_LANGUAGE;
        }
        if (in_array('cancelled', $fields)) {
            return self::CANCEL;
        }
        if (in_array('date', $fields)) {
            $old_value = $changeset['date'][0]->format('Y-m-d');
            $new_value = $changeset['date'][1]->format('Y-m-d');
            if ($old_value != $new_value) {
                return self::CHANGE_DATE;
            }
        }

        if (in_array('time', $fields)) {
            // figure out if the change of time crosses am/pm boundary
            $old_value = $changeset['time'][0]->format('H');
            $new_value = $changeset['time'][1]->format('H');
            if (($old_value < 13 && $new_value >= 13)
                or
            ($old_value >= 13 && $new_value < 13)) {
                return self::CHANGE_TIME_X_PM;
            } else {
                return self::CHANGE_TIME_WITHIN_AM_PM;
            }
        }

        return self::OTHER;
    }
}
