<?php
/** module/Admin/src/Service/ScheduleListener.php  */

namespace InterpretersOffice\Admin\Service;

use Zend\EventManager\Event;
use Zend\Log\LoggerInterface;
use Zend\Authentication\AuthenticationServiceInterface;
use InterpretersOffice\Entity;
use InterpretersOffice\Admin\Service\ScheduleListener;
use InterpretersOffice\Requests\Entity\Listener\RequestEntityListener;

/**
 * listener for schedule changes
 */
class ScheduleListener
{

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
                __CLASS__,$e->getName(),__FUNCTION__
                ));
                return;
            }
            return $this->$handler($e);
        }
        $this->logger->info(sprintf(
            'ScheduleListener not doing anything with %s: %s',
            $target,
            $e->getName()
        ));
    }
    protected function onUpdateRequest(Event $e)
    {
        $this->logger->debug(
            sprintf('handling request update in %s at %d',__METHOD__,__LINE__)
        );
        $listener_config = $this->config['event_listeners'];
        $request = $e->getParam('entity');
        // is it on the schedule?
        $scheduled_event = $request->getEvent();
        $this->logger->debug(
            __METHOD__.':  is it on the schedule? '.($scheduled_event ? 'yes':'no')
        );


    }
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
            case 'preRemove':
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
                $this->logger->info("$user has loaded event id "
                . $e->getParam('entity')->getId());
                break;

            default:
                $this->logger->info(sprintf(
                    'user %s is doing %s with event id %d',
                    $user,
                    $e->getName(),
                    $e->getParam('entity')->getId()
                ));
        }
    }
}
