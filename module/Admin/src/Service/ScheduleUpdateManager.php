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
     * the (most significant) user action
     * @var string
     */
    private $user_action;

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
        $user_action = $this->getUserAction($updates);

        $this->user_action = $user_action;
        $this->logger->debug(
             sprintf(__METHOD__.":\nuser action is '%s'  at %d", $user_action, __LINE__)
         );
        //$config = $this->config['event_listeners'];
        if (! isset($this->config['event_listeners'][$user_action])) {
            $this->logger->warn(__METHOD__.
            ":\nno configuration found for user action '$user_action'
                TO DO: IMPLEMENT");
            return $this->updateScheduledEvent($request,$updates);
        }
        // figure out what admin actions are configured for $user_action
        $actions = $this->getUserActions($request);//preg_grep($pattern, array_keys($config[$user_action]));
/*
XHRPOSThttps://office.localhost/requests/update/20572[HTTP/1.1 200 OK 51ms]
HeadersCookiesParamsResponseTimingsStack TraceSecurityPreviewResponse payload 1<br />2<b>
Recoverable fatal error</b>:  Object of class InterpretersOffice\Admin\Service\ScheduleUpdateManager
could not be converted to string in <b>/opt/www/court-interpreters-office/module/Admin/src/Service/ScheduleUpdateManager.php</b>
on line <b>214</b><br />3​
 */
        $filter = new DashToCamelCase();
        // and whether they are enabled
        foreach ($actions as $string) {
            $i = strrpos($string, '.') + 1;
            $action = substr($string, $i);
            $method = lcfirst($filter->filter($action));
            if ($config[$user_action][$string]) {
                if (method_exists($this, $method)) {
                    $this->logger->debug("we now need to call: $method()");
                    $this->$method($request, $updates);
                } else {
                    $this->logger->warn("!! not running not-implemented $method !");
                }
            } else {
                // explicity disabled in configuration
                $this->logger->debug("not running disabled action/method $method()");
            }
        }
        if ($user_action == self::OTHER) {
            $this->logger->debug(__METHOD__.":  it's the default user-action: other");
            $this->updateScheduledEvent($request,$updates);
        }

        return $this;
    }

    protected function getUserActions(Request $request)
    {
        $config = $this->config['event_listeners'];

        $type = (string)$request->getEventType()->getCategory()
            == 'in' ? 'in-court' : 'out-of-court';
        $language = (string) $request->getEvent()->getLanguage() == 'Spanish' ?
             'spanish' : 'non-spanish';
        $pattern = "/^(all-events|$type)\.(all-languages|$language)\./";

        // figure out what admin actions are configured for $user_action
        return preg_grep($pattern, array_keys($config[$user_action]));
    }


    /**
     * event listener for Request creation
     *
     * @param  Event  $e
     * @return void
     */
    protected function onCreateRequest(EventInterface $e)
    {
        $this->logger->debug(
            sprintf('handling request create in %s at %d', __METHOD__, __LINE__)
        );
        $listener_config = $this->config['event_listeners'];
    }

    /**
     * listener for request-cancellation event
     *
     * @param  EventInterface $e
     * @return void
     */
    protected function onCancelRequest(EventInterface $e)
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
     * @todo see if this can be less complicated
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
            /** @var Doctrine\ORM\UnitOfWork $uow */
            $uow = $this->objectManager->getUnitOfWork();
            foreach ($interpreterEvents as $shit) {
                $uow->scheduleForDelete($shit);
            }
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
        $this->logger->info("notifying interpreters about $this->user_action");
        $event = $request->getEvent();
        $interpreterEvents = $event->getInterpreterEvents();
        $count = $interpreterEvents->count();
        if (! $count) {
            return $this;
        }
        //$this->logger->debug("there are/were ".$interpreterEvents->count());
        foreach ($interpreterEvents as $ie) {
            $email = $ie->getInterpreter()->getEmail();
            $this->logger->debug("need to email: $email");
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
    private function getUserAction(Array $changeset)
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
