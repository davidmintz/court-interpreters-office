<?php
/** module/Admin/src/Service/ScheduleListener.php  */

namespace InterpretersOffice\Admin\Service;

use Zend\EventManager\Event;
use Zend\Log\LoggerInterface;
use Zend\Authentication\AuthenticationServiceInterface;
use InterpretersOffice\Entity;
use InterpretersOffice\Admin\Service\ScheduleListener;

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
     * constructor
     *
     * @param LoggerInterface                $log
     * @param AuthenticationServiceInterface $auth
     */
    public function __construct(LoggerInterface $log, AuthenticationServiceInterface $auth)
    {
        $this->logger = $log;
        $this->auth = $auth;
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
        $this->logger->info(sprintf(
            'ScheduleListener not doing anything with %s:%s',
            $target,
            $e->getName()
        ));
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
