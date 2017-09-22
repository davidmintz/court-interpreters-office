<?php

namespace InterpretersOffice\Entity\Listener;


use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;

use Zend\Log\LoggerAwareInterface;
use Zend\Log\LoggerInterface;

// maybe not
// use InterpretersOffice\Entity\Repository\CacheDeletionInterface;

use InterpretersOffice\Entity;
use Zend\Log;

class EventEntityListener implements  EventManagerAwareInterface, LoggerAwareInterface
{
	use Log\LoggerAwareTrait;
	use EventManagerAwareTrait;

	/**
     * callback
     *
     * runs when Event entity is loaded. not doing anything at the moment
     * other than test that the listener is wired up.
     *
     * @param Entity\Event $eventEntity
     * @param LifecycleEventArgs $event
     */
    public function postLoad(Entity\Event $eventEntity, LifecycleEventArgs $event)
    {        
        $this->getEventManager()->trigger(__FUNCTION__, $this);        
        $this->logger->debug("running ".__FUNCTION__ . " in your EventEntityListener ...");        
    }
    
    /**
     * prePersist
     * 
     * @param \InterpretersOffice\Entity\Event $eventEntity
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Entity\Event $eventEntity, LifecycleEventArgs $event)
    {
        $this->logger->debug("running ".__FUNCTION__ . " in your EventEntityListener ..."); 
    }
    
}