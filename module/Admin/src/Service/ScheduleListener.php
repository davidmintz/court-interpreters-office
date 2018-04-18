<?php

/** module/Admin/src/Service/ScheduleListener.php  */

namespace InterpretersOffice\Admin\Service;

use Zend\EventManager\Event;
use Zend\Log\Logger;
use InterpretersOffice\Entity;

use Zend\Authentication\AuthenticationServiceInterface;

class ScheduleListener

{


    private $logger;

    private $auth;

    public function __construct(Logger $log, AuthenticationServiceInterface $auth)
    {
        $this->logger = $log;
        $this->auth = $auth;

    }

    public function doShit(Event $e)
    {
        $this->logger->info("doing shit in ScheduleListener because of ".$e->getName());
        if (stristr($e->getName(),'remove')) {

        }
        $repo = $e->getParam('args')->getEntityManager()->getRepository(Entity\Event::class);
        $entity = $e->getParam('eventEntity');
        $data = $repo->getView($entity->getId());
        $this->logger->info(json_encode($data));

    }


}
