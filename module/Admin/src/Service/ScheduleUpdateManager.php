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
use Doctrine\ORM\Event\OnFlushEventArgs;

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
        $this->logger->debug(
            __METHOD__.": request is a ".get_class($request)
        );
        $event = $request->getEvent();
        $this->logger->debug(
            __METHOD__.": event is a ".(is_object($event) ? get_class($event):gettype($event))
        );
        if (! $event) {
            $this->logger->debug(
                __FUNCTION__.": request apparently NOT scheduled, no corresponding event"
            );
            return;
        }
        $em =  $e->getParam('entity_manager');
        // DOES NOT WORK
        // $uow = $em->getUnitOfWork();
        // $changeset = $uow->getEntityChangeSet($request);
        // $this->logger->debug(
        //      'changed:  '.print_r(array_keys($changeset),true)
        //  );

        // IS TOO LABORIOUS
        // $session = new \Zend\Session\Container('request_updates');
        // $previous = $session->{$request->getId()};
        // $this->logger->debug(
        //     print_r($previous,true)
        // );
        // $changed_fields = [];
        // // date and time
        // if ($request->getTime()->format('g:i a')) != $previous['time']) {
        //     $changed_fields[] = 'time';
        // }
        /*
        (
    [id] => 20568
    [time] => DateTime Object
        (
            [date] => 1970-01-01 14:00:00.000000
            [timezone_type] => 3
            [timezone] => America/New_York
        )

    [date] => DateTime Object
        (
            [date] => 2019-03-01 00:00:00.000000
            [timezone_type] => 3
            [timezone] => America/New_York
        )

    [docket] => 2014-CR-0133
    [type] => sentence
    [language] => Spanish
    [comments] => some shit here bla bla woof boink say shit
    [modified] => DateTime Object
        (
            [date] => 2019-02-08 18:50:47.000000
            [timezone_type] => 3
            [timezone] => America/New_York
        )

    [modified_by_lastname] => Daniels
    [modified_by_firstname] => Anthony
    [modified_by_hat] => Courtroom Deputy
    [created] => DateTime Object
        (
            [date] => 2019-01-09 10:51:55.000000
            [timezone_type] => 3
            [timezone] => America/New_York
        )

    [submitter_lastname] => Daniels
    [submitter_firstname] => Anthony
    [submitter_hat] => Courtroom Deputy
    [location] => 12C
    [parent_location] => 500 Pearl
    [event_id] => 116445
    [pending] =>
    [event_date] => DateTime Object
        (
            [date] => 2019-03-01 00:00:00.000000
            [timezone_type] => 3
            [timezone] => America/New_York
        )

    [event_time] => DateTime Object
        (
            [date] => 1970-01-01 13:00:00.000000
            [timezone_type] => 3
            [timezone] => America/New_York
        )

    [cancellation] =>
    [judge_lastname] => Woods
    [judge_firstname] => Gregory
    [judge_middlename] => H.
    [judge_flavor] => USDJ
    [defendants] => Array
        (
            [0] => Array
                (
                    [surnames] => Ontiveros Mesa
                    [given_names] => Jose Ramon
                )

        )

    [interpreters] => Array
        (
        )

)


        */

        // $changed_fields = array_keys($this->preUpdateEventArgs->getEntityChangeSet());
        // $shit = print_r($changed_fields,true);
        // $this->logger->debug(__METHOD__.": changed: $shit");
        //$this->logger->debug(__METHOD__.": ".gettype($this->preUpdateEventArgs)." is our data type  !!");


        /**
         * @todo CONTINUE with this  !!!
         */


        // /** @var Doctrine\ORM\Event\OnFlushEventArgs $args */
        // $args = $e->getParam('onFlushEventArgs');
        // /** @var Doctrine\ORM\UnitOfWork $uow */
        // $uow = $args->getEntityManager()->getUnitOfWork();

        // $entities = $uow->getScheduledEntityUpdates();
        // $request = null;
        // $scheduled_event = null;
        // foreach ($entities as $entity) {
        //     if ($entity instanceof Request) {
        //         $request = $entity;
        //         break;
        //     }
        //     // if ($entity instanceof Entity\Event) {
        //     //     $scheduled_event = $entity;
        //     //     //break;
        //     // }
        // }
        // ///*
        // if (! $request) {
        //     // they may have submitted the form without changing entity props,
        //     // or it may be an insert
        //     $this->logger->debug(
        //         __METHOD__.": looks like NOT a request entity update, returning"
        //     );
        //     return;
        // }
        //
        // $scheduled_event = $request->getEvent();
        // if (! $scheduled_event) {
        //     $this->logger->debug(
        //         __METHOD__.": request has no corresponding event, returning"
        //     );
        //     return;
        // } else {
        //     $this->logger->debug(__METHOD__.": \$scheduled_event is a ".get_class($scheduled_event));
        // }
        //
        // $changeset = $uow->getEntityChangeSet($request);
        // $user_action = $this->getUserAction($changeset);
        // $this->user_action = $user_action;
        // $fields_modified = implode(', ', array_keys($changeset));
        // $this->logger->debug(
        //     "fields modified? ".($fields_modified ?: 'not shit')
        // );
        //
        // $this->logger->debug(
        //     sprintf(__METHOD__.":\nuser action is '%s'  at %d", $user_action, __LINE__)
        // );
        // $config = $this->config['event_listeners'];
        // if (! isset($config[$user_action])) {
        //     $this->logger->warn(__METHOD__.
        //     ":\nno configuration found for user action '$user_action'
        //         TO DO: IMPLEMENT");
        //     return $this->updateScheduledEvent($request, $args);
        // }
        // $type = (string)$request->getEventType()->getCategory()
        //     == 'in' ? 'in-court' : 'out-of-court';
        // $language = (string) $scheduled_event->getLanguage() == 'Spanish' ?
        //      'spanish' : 'non-spanish';
        // $pattern = "/^(all-events|$type)\.(all-languages|$language)\./";
        //
        // // figure out what admin actions are configured for $user_action
        // $actions = preg_grep($pattern, array_keys($config[$user_action]));
        // //if (! $actions) {}
        //
        // $filter = new DashToCamelCase();
        // // and whether they are enabled
        // foreach ($actions as $string) {
        //     $i = strrpos($string, '.') + 1;
        //     $action = substr($string, $i);
        //     $method = lcfirst($filter->filter($action));
        //     if ($config[$user_action][$string]) {
        //         if (method_exists($this, $method)) {
        //             $this->logger->debug("need to call: $method()");
        //             $this->$method($request, $args);
        //         } else {
        //             $this->logger->warn("not running not-implemented $method !");
        //         }
        //     } else {
        //         // explicity disabled in configuration
        //         $this->logger->debug("not running disabled $method()");
        //     }
        // }
        // if ($user_action == self::OTHER) {
        //     $this->logger->debug(__METHOD__.":  it's the default user-action: other");
        //     $this->updateScheduledEvent($request, $args);
        // }
        // if ($this->event_was_updated) {
        //     $em = $args->getEntityManager();
        //     $this->logger->debug("recomputing entity changeset for event id: "
        //         .$scheduled_event->getId());
        //     $uow->recomputeSingleEntityChangeSet(
        //         $em->getClassMetadata(get_class($scheduled_event)),
        //         $scheduled_event
        //     );
        // }

        return $this;
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
     * @param  OnFlushEventArgs  $args
     * @return ScheduleUpdateManager
     *
     */
    public function updateScheduledEvent(Request $request, OnFlushEventArgs $args)
    {
        /** @todo figure out defendants, including xtra data */
        /** @todo figure out how to handle updated comments */

        $uow = $args->getEntityManager()->getUnitOfWork();
        $changeset = $uow->getEntityChangeSet($request);
        $event = $request->getEvent();
        if (! $event) {
            $this->logger->debug(__METHOD__.": no scheduled event, nothing to do");
            return; // important! from here on we might just assume...
        }
        $props = [
            'date','time','judge','language','eventType','docket','location'
        ];
        $updatable = array_intersect($props, array_keys($changeset));
        $shit = print_r(array_keys($changeset), true);
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
     * @param  OnFlushEventArgs  $args
     * @return ScheduleUpdateManager
     *
     */
    public function deleteScheduledEvent($request, $args)
    {
        $em = $args->getEntityManager();
        $event = $request->getEvent();
        $em->remove($event);
        $this->logger->debug(__METHOD__.": we have REMOVED scheduled event id ".$event->getId());
    }

    /**
     * un-assigns all interpreters from Request's corresponding Event
     *
     * @todo see if this can be less complicated
     *
     * @param  Request $request
     * @param  OnFlushEventArgs  $args
     * @return ScheduleUpdateManager
     */
    public function removeInterpreters(Request $request, OnFlushEventArgs $args)
    {
        $event = $request->getEvent();
        $interpreterEvents = $event->getInterpreterEvents();
        if ($n = $interpreterEvents->count()) {
            /** @var Doctrine\ORM\UnitOfWork $uow */
            $uow = $args->getEntityManager()->getUnitOfWork();
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
     * @param  OnFlushEventArgs  $args
     * @return ScheduleUpdateManager
     */
    public function notifyAssignedInterpreters(Request $request, OnFlushEventArgs $args)
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
