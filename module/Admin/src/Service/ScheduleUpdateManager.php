<?php
/** module/Admin/src/Service/ScheduleUpdateManager.php  */

namespace InterpretersOffice\Admin\Service;

use Zend\EventManager\EventInterface;
use Zend\Log\LoggerInterface;
use Zend\Authentication\AuthenticationServiceInterface;
use InterpretersOffice\Entity;
use InterpretersOffice\Requests\Entity\Request;
use InterpretersOffice\Requests\Entity\Listener\RequestEntityListener;
use InterpretersOffice\Admin\Service\ScheduleUpdateManager;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
//use Doctrine\ORM\Event\OnFlushEventArgs;

use Zend\Filter\Word\DashToCamelCase;

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
     * experimental
     * @var array
     */
    protected $previous_state;

    /**
     * [private description]
     * @var [type]
     */
    private $objectManager;

    /**
     * whether Request's related Event was modified.
     *
     * this flag tell us whether to recomputeSingleEntityChangeSet() in case
     * the Event entity corresponding to a Request has been updated
     *
     * @var boolean
     */
    private $event_was_updated;

    /**
     * the (most significant) user event/action
     * @var string
     */
    private $user_event;

    /**
     * constructor
     *
     * @param AuthenticationServiceInterface $auth
     * @param LoggerInterface                $log
     * @param Array                          $config
     */
    public function __construct(

        AuthenticationServiceInterface $auth,
        LoggerInterface $log,
        Array $config
    ) {
        $this->logger = $log;
        $this->auth = $auth;
        $this->config = $config;
    }

    public function setPreviousState(Request $request)
    {
        // we are interested in: date, time, event-type, language, docket,
        // judge, defendants, location, comments, xtra data

        $this->previous_state = [
            'date' =>  $request->getDate(),
            'time' =>  $request->getTime(),
            'language' => $request->getLanguage(),
            'eventType' =>  $request->getEventType(),
            'location' =>  $request->getLocation(),
            'docket'  => $request->getDocket(),
            'comments' => $request->getComments(),
            'judge' =>  $request->getJudge(),
            'extraData' => $request->getExtraData(),
            'defendants' => $request->getDefendants()->toArray(),
        ];

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
     * event listener for Request update
     *
     * @param  Event  $e
     * @return void
     */
    public function onUpdateRequest(EventInterface $e)
    {
        $this->logger->debug(
            __METHOD__.": here we GO !!!!!!!"
        );
        $request = $e->getParam('request');
        $event = $request->getEvent();
        if (! $event) {
            $this->logger->debug(
                __FUNCTION__.": request apparently NOT scheduled, no corresponding event"
            );
            return;
        }
        $em =  $e->getParam('entity_manager');
        $this->objectManager = $em;
        $previous = $this->previous_state;
        $updates = [];
        foreach ($previous as $field => $value) {
            $after = $request->{'get'.ucfirst($field)}();
            if ($field == 'defendants') {
                $after = $after->toArray();
            }
            if ($previous[$field] != $after) {
                $updates[$field] = [$previous[$field],$after];
            }
        }
        $this->logger->debug(
            'what was modified: '.implode(', ',array_keys($updates))
        );
        $user_event = $this->getUserEvent($updates);
        $this->user_event = $user_event;
        $this->logger->debug(
             sprintf(__METHOD__.":\nuser-event is '%s'  at %d", $user_event, __LINE__)
         );
        if (! isset($this->config['event_listeners'][$user_event])) {
            $this->logger->warn(__METHOD__.
            ":\nno configuration found for user-event '$user_event'
                TO DO: IMPLEMENT");

            return $this->updateScheduledEvent($request,$updates);
        }

        // figure out what admin actions are configured for $user_event
        $actions = $this->getUserActions($request,$user_event);
        $filter = new DashToCamelCase();
        $config = $this->config['event_listeners'];
        // and whether they are enabled
        foreach ($actions as $string) {
            $i = strrpos($string, '.') + 1;
            $action = substr($string, $i);
            $method = lcfirst($filter->filter($action));
            if ($config[$user_event][$string]) {
                if (method_exists($this, $method)) {
                    $this->logger->debug("we now need to call: $method()");
                    $this->$method($request, $updates);
                } else {
                    $this->logger->warn("!! not running not-implemented method: $method !");
                }
            } else {
                // explicity disabled in configuration
                $this->logger->debug("not running explicitly disabled action/method: $method()");
            }
        }
        if ($user_event == self::OTHER) {
            $this->logger->debug(__METHOD__.
                ":  it's the default user-action: other");
            $this->updateScheduledEvent($request,$updates);
        }

        return $this;
    }

    /**
     * figures out what admin actions (methods) to run based on $user_event
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
            sprintf('handling request create in %s at %d', __METHOD__, __LINE__)
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
    }


    /**
     * updates an Event entity to synchronize with Request
     *
     * @param  Request $request
     * @param Array updates
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
        $this->logger->warn("what changed:\n$shit");
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
            $this->event_was_updated = true;
        }
        if (isset($updates['defendants'])) {
            $this->logger->debug("they updated the defendants. need to get busy");
            // were they the same before the Request update?
            $ours = $event->getDefendants()->toArray();
            $was_the_same = $ours = $updates['defendants'][0];
            $this->logger->debug("were they the same before? ".($was_the_same?"YES":"NO"));

            if ($was_the_same) {
                 $theirs = $request->getDefendants()->toArray();
                 //$log->info("trying to update event defts");
                 $to_be_added = array_diff($theirs,$ours);
                 foreach ($to_be_added as $d) {
                      $this->logger->info("need to add? ".$d->getSurnames());
                      $event->addDefendant($d);
                 }
                 $to_be_removed = array_diff($ours,$theirs);
                 foreach ($to_be_removed as $d) {
                      $this->logger->info("need to remove? ".$d->getSurnames());
                      $event->removeDefendant($d);
                 }
            }
        }

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
    public function deleteScheduledEvent($request, Array $updates)
    {

        $this->objectManager->remove($event);
        $this->logger->debug(__METHOD__.": we have REMOVED scheduled event id ".$event->getId());
        return $this;
    }

    /**
     * un-assigns all interpreters from Request's corresponding Event
     *
     * second parameter is not used, but is there to keep our signature
     * consistent with that of other methods
     *
     * @param  Request $request
     * @param array $updates
     * @return ScheduleUpdateManager
     */
    public function removeInterpreters(Request $request, Array $updates)
    {
        $event = $request->getEvent();
        $interpreterEvents = $event->getInterpreterEvents();
        if ($n = $interpreterEvents->count()) {
            $event->removeInterpreterEvents($interpreterEvents);
            $this->logger->debug(__METHOD__.": we removed $n interpreters");
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
    public function notifyAssignedInterpreters(Request $request, array $updates)
    {
        $event = $request->getEvent();
        $interpreterEvents = $event->getInterpreterEvents();
        $count = $interpreterEvents->count();
        $message = sprintf(
            'ScheduleUpdateManager: notifying %d interpreters about user action %s with request id %s',
            $count, $this->user_event, $request->getId()
        );
        $this->logger->info($message);
        if (! $count) {
            return $this;
        }

        foreach ($interpreterEvents as $ie) {
            $email = $ie->getInterpreter()->getEmail();
            $this->logger->info("need to email: $email");
            $message = $this->createEmailMessage('<p>hi there</p>', 'hi there');
            $this->logger->debug("we have created a ". get_class($message) . " for $email");
        }

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
