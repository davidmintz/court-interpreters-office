<?php
/** module/Admin/src/Service/ScheduleListener.php  */

namespace InterpretersOffice\Admin\Service;

use Zend\EventManager\Event;
use Zend\Log\LoggerInterface;
use Zend\Authentication\AuthenticationServiceInterface;
use InterpretersOffice\Entity;
use InterpretersOffice\Admin\Service\ScheduleListener;
use InterpretersOffice\Requests\Entity\Listener\RequestEntityListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

use Zend\Filter\Word\DashToCamelCase;

/**
 * listener for schedule changes
 */
class ScheduleListener
{

    const CHANGE_DATE = 'change-date';

    const CHANGE_TIME_X_PM = '';

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
     * @return ScheduleListener;
     */
    public function setAuth(AuthenticationServiceInterface $auth)
    {
        $this->auth = $auth;

        return $this;
    }

    /**
     * scheduleChange observer
     *
     * @param  Event  $e
     * @return void
     */
    public function scheduleChange(Event $e)
    {
        $target = is_object($e->getTarget()) ? get_class($e->getTarget())
            : $e->getTarget();
        $this->logger->debug("ScheduleListener observing ".$e->getName()
            . " on target $target");
        if (Entity\Listener\EventEntityListener::class == $target) {
            return $this->eventUpdateHandler($e);
        }
        if (RequestEntityListener::class == $target) {
            $this->logger->debug(
                "ScheduleListener triggered by RequestEntityListener: "
                .$e->getName());

            $handler = 'on' . ucfirst($e->getName()) . 'Request';
            if (! method_exists($this, $handler)) {
                $this->logger->warn(sprintf(
                    '%s has no handler for event %s in %s',
                __CLASS__,$e->getName(),__FUNCTION__));

                return;
            }
            return $this->$handler($e);
        }
        $this->logger->info(sprintf(
            'hello, ScheduleListener not doing anything with %s: %s',
            $target, $e->getName()
        ));
    }

    /**
     * event listener for Request update
     *
     * @param  Event  $e
     * @return void
     */
    protected function onUpdateRequest(Event $e)
    {
        $this->logger->debug(
            sprintf('handling request update in %s at %d',__METHOD__,__LINE__)
        );
        /**
         * @var \InterpretersOffice\Requests\Entity\Request $request
         */
        $request = $e->getParam('entity');
        // is it on the schedule?
        /**
         * @var Entity\Event $scheduled_event
         */
        $scheduled_event = $request->getEvent();

        $this->logger->debug(
            __METHOD__.':  on the schedule? '.($scheduled_event ? 'yes':'no')
        );
        if (! $scheduled_event) {
            return;
        }
        $listener_config = $this->config['event_listeners'];
        /**
        * @var PreUpdateEventArgs $args
        */
        $args = $e->getParam('args');

        $user_action = $this->getUserActionName($args);
        $this->logger->debug("the FUCK??????");
        $em = $args->getEntityManager();
        $this->logger->debug("change of date? ".($args->hasChangedField('date')?"YES":"NO"));
        if ($args->hasChangedField('date')) {
            //$this->logger->debug("NOT dealing with date change");
            $type = (string)$request->getEventType()->getCategory()
              == 'in' ? 'in-court':'out-of-court';
            $language = (string) $scheduled_event->getLanguage() == 'Spanish' ?
                 'spanish':'non-spanish';

            $pattern = "/^(all-events|$type)\.(all-languages|$language)\./";

            // figure out what actions are configured
            $actions = preg_grep($pattern,array_keys($listener_config['change-date']));
            $filter = new DashToCamelCase();
            // and whether they are enabled
            foreach ($actions as $string) {
                $i = strrpos($string,'.') + 1;
                $action = substr($string,$i);
                // $this->logger->debug("do $action?  ".
                //     ($listener_config['change-date'][$string] ? "YES":"NO"));
                if ($listener_config['change-date'][$string]) {
                    $method = lcfirst($filter->filter($action));
                    $this->logger->debug("need to call: $method()");
                }
            }

        } else {
            $this->logger->debug("NOT dealing with date change");
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
                'ScheduleListener is running %s with %s',
                __FUNCTION__,
                $e->getName()
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
                $this->logger->info("ScheduleListener: $user has loaded event id "
                . $e->getParam('entity')->getId());
                break;

            default:
                $this->logger->info(sprintf(
                    'ScheduleListener: user %s is doing %s with event id %d',
                    $user,
                    $e->getName(),
                    $e->getParam('entity')->getId()
                ));
        }
    }

    private function getUserActionName(PreUpdateEventArgs $args)
    {

        if ($args->hasChangedField('date')) {
            return 'change-date';
        }
        if ($args->hasChangedField('time')) {
            $old_value = $args->getOldValue('time')->format('H');
            $new_value = $args->getNewValue('time')->format('H');
            if ($old_value < 13 && $new_value >= 13) {

            }

        }
    }
}
