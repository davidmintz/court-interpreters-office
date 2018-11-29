<?php
/** module/Admin/src/Service/ScheduleUpdateManager.php  */

namespace InterpretersOffice\Admin\Service;

use Zend\EventManager\EventInterface;
use Zend\Log\LoggerInterface;
use Zend\Authentication\AuthenticationServiceInterface;
use InterpretersOffice\Entity;
use InterpretersOffice\Requests\Entity\Request;
use InterpretersOffice\Requests\Entity\Listener\RequestEntityListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

use Zend\Filter\Word\DashToCamelCase;

/**
 * Reacts to changes in Request entities.
 *
 * May also eventually be pressed into service for doing things in response to
 * changes in Event entities.
 */
class ScheduleUpdateManager
{

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
     * constructor
     *
     * @param LoggerInterface                $log
     * @param AuthenticationServiceInterface $auth
     * @param Array                          $config
     */
    public function __construct(AuthenticationServiceInterface $auth,
        LoggerInterface $log, Array $config)
    {
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
     * @todo rewrite/rename this whole thing
     *
     * @param  Event  $e
     * @return void
     */
    public function onUpdateRequest(EventInterface $e)
    {
        $this->logger->debug(
            sprintf(__METHOD__.': handling request update in %s at %d',
            __METHOD__,__LINE__)
        );
        /** @var Doctrine\ORM\Event\OnFlushEventArgs $args */
        $args = $e->getParam('onFlushEventArgs');
        /** @var Doctrine\ORM\UnitOfWork $uow */
        $uow = $args->getEntityManager()->getUnitOfWork();
        $entities = $uow->getScheduledEntityUpdates();
        $request = null;
        $event = null;
        foreach ($entities as $entity) {
            if ($entity instanceof Request) {
                $request = $entity;
                break;
            }
        }
        // this is a little aggressive because $request could be null, though
        // if everything is wired correctly, it should not be
        $scheduled_event = $request->getEvent();
        if (! $scheduled_event) {
            $this->logger->debug(
                __METHOD__.": request has no corresponding event, returning");
            return;
        }

        $changeset = $uow->getEntityChangeSet($request);

        $user_action = $this->getUserAction($changeset);
        $this->logger->debug(
            sprintf(__METHOD__.': user action is "%s"  at %d',$user_action,__LINE__)
        );

        //$event_was_updated = false;
        $type = (string)$request->getEventType()->getCategory()
            == 'in' ? 'in-court':'out-of-court';
        $language = (string) $scheduled_event->getLanguage() == 'Spanish' ?
             'spanish':'non-spanish';
        $pattern = "/^(all-events|$type)\.(all-languages|$language)\./";
        $listener_config = $this->config['event_listeners'];
        // $this->logger->debug("pattern: $pattern; language $language; type $type");
        // $this->logger->debug(print_r($listener_config,true));

        // figure out what admin actions are configured for $user_action
        $actions = preg_grep($pattern,array_keys($listener_config[$user_action]));
        if (! $actions) {
            $this->logger->debug(__METHOD__.
            ": no configuration found for user action $user_action (pattern $pattern)");
        }

        $filter = new DashToCamelCase();
        // and whether they are enabled
        foreach ($actions as $string) {
            $i = strrpos($string,'.') + 1;
            $action = substr($string,$i);
            $method = lcfirst($filter->filter($action));
            if ($listener_config[$user_action][$string]) {
                if (method_exists($this, $method)) {
                    $this->logger->debug("need to call: $method()");
                    $this->$method($request,$changeset);
                } else {
                    $this->logger->warn("not running not-implemented $method");
                }
            } else {
                // explicity disabled in configuration
                $this->logger->debug("not running disabled $method()");
            }
        }
        $em = $args->getEntityManager();
        if ($this->event_was_updated) {
            $this->logger->debug("trying to update event id: ".$scheduled_event->getId());
            $uow->recomputeSingleEntityChangeSet(
                $em->getClassMetadata(get_class($scheduled_event)),$scheduled_event
            );
        }

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
            sprintf('handling request create in %s at %d',__METHOD__,__LINE__)
        );
        $listener_config = $this->config['event_listeners'];
    }
    protected function onCancelRequest(EventInterface $e)
    {
        $this->logger->debug(
            sprintf('handling request cancel in %s at %d',__METHOD__,__LINE__)
        );
    }


    /**
     * eventUpdateHandler
     *
     * observes update, delete, etc on Event entities. Still a WIP.
     *
     * @param  Event  $e
     * @return void
     */
    public function eventUpdateHandler(EventInterface $e)
    {
        $user = $this->auth->getIdentity()->username;
        $this->logger->debug(
            sprintf(
                __CLASS__ .': running %s with %s',
                __FUNCTION__,  $e->getName()
            )
        );
        switch ($e->getName()) {
            // case 'preRemove':
            //     break;
            case 'postRemove':
                $repo = $e->getParam('args')->getEntityManager()
                ->getRepository(Entity\Event::class);
                $entity = $e->getParam('entity');
                $data = $repo->getView($entity->getId());
                $info = [
                'user' => $user,
                'action' => $e->getName(),
                'event_data' => $data,
                ];
                $this->logger->info(json_encode($info));
                break;

            case 'postLoad':
                $this->logger->info(__METHOD__.": $user has loaded event id "
                . $e->getParam('entity')->getId());
                break;

            default:
                $this->logger->info(sprintf(
                    __METHOD__.': user %s is doing %s with event id %d',
                    $user,
                    $e->getName(),
                    $e->getParam('entity')->getId()
                ));
        }
    }
    /**
     * updates an Event entity to synchronize with Request
     *
     * @param  Request $request   Request entity
     * @param  array   $changeset Request fields that were modified
     * @return ScheduleUpdateManager
     */
    public function updateScheduledEvent(Request $request, array $changeset)
    {
        /** @todo figure out defendants, including xtra data */
        /** @todo figure out how to handle updated comments */
        $event = $request->getEvent();
        if (! $event) {
            $this->logger->debug(__METHOD__.": no scheduled event, nothing to do");
        }
        $props = [
            'date','time','judge','language','eventType','docket','location'
        ];
        $updatable = array_intersect($props, array_keys($changeset));
        if ($updatable) {
            foreach($updatable as $prop) {
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
        if (in_array('language',$fields)) {
            return self::CHANGE_LANGUAGE;
        }
        if (in_array('cancelled',$fields)) {
            return self::CANCEL;
        }
        if (in_array('date',$fields)) {
            $old_value = $changeset['date'][0]->format('Y-m-d');
            $new_value = $changeset['date'][1]->format('Y-m-d');
            if ($old_value != $new_value) {
                return self::CHANGE_DATE;
            }
        }
        if (in_array('time',$fields)) {

            $old_value = $changeset['time'][0]->format('H');
            $new_value = $changeset['time'][1]->format('H');
            if (($old_value < 13 && $new_value >= 13)
                or
            ($old_value >= 13 && $new_value < 13))
            {
                return self::CHANGE_TIME_X_PM;
            } else {
                return self::CHANGE_TIME_WITHIN_AM_PM;
            }
        }
        return 'other';
    }
}
