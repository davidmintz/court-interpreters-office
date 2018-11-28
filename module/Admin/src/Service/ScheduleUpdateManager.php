<?php
/** module/Admin/src/Service/ScheduleUpdateManager.php  */

namespace InterpretersOffice\Admin\Service;

use Zend\EventManager\Event;
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
    public function onUpdateRequest(Event $e)
    {
        $this->logger->debug(
            sprintf(__METHOD__.': handling request update in %s at %d',__METHOD__,__LINE__)
        );
        /** */
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
        // this is a little agressive because $request could be null
        $scheduled_event = $request->getEvent();
        if (! $scheduled_event) {
            $this->logger->debug(
                __METHOD__.": request has no corresponding event, returning");
            return;
        }
        // $request is a Request entity
        $changeset = $uow->getEntityChangeSet($request);

        $user_action = $this->getUserActionName($changeset);
        $this->logger->debug(
            sprintf(__METHOD__.': user action is "%s"  at %d',$user_action,__LINE__)
        );

        $event_was_updated = false;
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

        $filter = new DashToCamelCase();
        // and whether they are enabled
        foreach ($actions as $string) {
            $i = strrpos($string,'.') + 1;
            $action = substr($string,$i);
            $method = lcfirst($filter->filter($action));
            if ($listener_config[$user_action][$string]) {
                $this->logger->debug("need to call: $method()");
                if (method_exists($this, $method)) {
                    $this->$method($e,$action);
                } else {
                    $this->logger->warn("not running not-implemented $method");
                }
            } else {
                $this->logger->debug("not running disabled $method()");
            }
        }
        // to be continued
        return;

        foreach ($changeset as $field => $values) {
            if ($field == 'time' or $field == 'date') {
                $this->logger->debug("change of $field noted in ".__METHOD__);
                //$event = $request->getEvent();
                $event->{'set'.ucfirst($field)}($request->{'get'.ucfirst($field)}());
                $event->setComments(
                    'woo hoo fuck yes it worked at '.date('H:i:s')
                );
                $this->logger->debug("reset time to: ".$event->getTime()->format('H:i'));
                $event_was_updated = true;
            }
        }
        $em = $args->getEntityManager();
        if ($event_was_updated) {
            $this->logger->debug("trying to update event id: ".$event->getId());
            $uow->recomputeSingleEntityChangeSet(
                $em->getClassMetadata(get_class($event)),$event
            );
        }

    }


    /**
     * event listener for Request creation
     *
     * @param  Event  $e
     * @return void
     */
    protected function onCreateRequest(Event $e)
    {
        $this->logger->debug(
            sprintf('handling request create in %s at %d',__METHOD__,__LINE__)
        );
        $listener_config = $this->config['event_listeners'];
    }
    protected function onCancelRequest(Event $e)
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
    public function eventUpdateHandler(Event $e)
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
    private function getUserActionName(Array $changeset)
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
