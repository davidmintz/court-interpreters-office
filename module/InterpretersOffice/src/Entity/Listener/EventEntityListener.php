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
     * runs when Interpreter entity is loaded
     *
     * which in turn doesn't do anything
     *
     * @param Interpreter $interpreter
     * @param LifecycleEventArgs $event
     */
    public function postLoad(Entity\Event $eventEntity, LifecycleEventArgs $event)
    {

        $this->getEventManager()->trigger(__FUNCTION__, $this);
        //var_dump(is_null($this->log));
        $this->log->debug("running ".__FUNCTION__ . " in your EventEntityListener ...");
        //echo "Interpreter entity listener WTF? hello what the FUCKING FUCK??";
    }
    
}