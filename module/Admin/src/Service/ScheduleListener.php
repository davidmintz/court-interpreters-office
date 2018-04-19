<?php

/** module/Admin/src/Service/ScheduleListener.php  */

namespace InterpretersOffice\Admin\Service;

use Zend\EventManager\Event;
use Zend\Log\LoggerInterface;
use InterpretersOffice\Entity;

use Zend\Authentication\AuthenticationServiceInterface;

/**
 * listener for scheduling events
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

    public function doShit(Event $e)
    {
        $this->logger->info("doing shit in ScheduleListener because of ".$e->getName());
        $this->logger->info(
            'user: '. $this->auth->getIdentity()->username

        );
        $target = is_object($e->getTarget())? get_class($e->getTarget())
            : $e->getTarget();
        if (Entity\Event\Listener\EventEntityListener::class == $target) {
            if (strstr('remove',$e->getName())) {

            }
        }
        $repo = $e->getParam('args')->getEntityManager()->getRepository(Entity\Event::class);
        $entity = $e->getParam('eventEntity');
        $data = $repo->getView($entity->getId());
        $this->logger->info(json_encode($data));

    }


}
