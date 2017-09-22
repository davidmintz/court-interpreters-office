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
use Zend\Authentication\AuthenticationServiceInterface;
use Doctrine\ORM\EntityManager;

class EventEntityListener implements  EventManagerAwareInterface, LoggerAwareInterface
{
	use Log\LoggerAwareTrait;
	use EventManagerAwareTrait;
    
    
    
    /**
     *
     * @var AuthenticationServiceInterface
     */
    protected $auth;
    
    /**
     * sets authentication service
     * 
     * @param AuthenticationServiceInterface $auth
     * @return \InterpretersOffice\Entity\Listener\EventEntityListener
     */
    public function setAuth(AuthenticationServiceInterface $auth)
    {
        $this->auth = $auth;
        return $this;
    }
    /**
     * preUpdate callback
     *
     * @todo if modified, set metadata accordingly
     * 
     * @param Entity\Event $eventEntity
     * @param LifecycleEventArgs $event
     */
    public function preUpdate(Entity\Event $eventEntity, 
            LifecycleEventArgs $event)
    {
        
    }
	/**
     * postLoad callback
     *
     * not doing anything at the moment other than test that the 
     * listener is wired up.
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
     * prePersist callback
     * 
     * @param \InterpretersOffice\Entity\Event $eventEntity
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Entity\Event $eventEntity, LifecycleEventArgs $event)
    {        
        // set the CreatedBy user
        if (! $eventEntity->getCreatedBy()) {
            // because in test environment might already have been done
            $user = $this->getAuthenticatedUser($event->getEntityManager());
            $eventEntity->setCreatedBy($user);
        }
        $now = new \DateTime();
        $eventEntity->setCreated($now)
                ->setModifiedBy(null)
                ->setModified(null);
        $this->logger->debug(__FUNCTION__ . " in EventEntityListener did shit");
    }
    
    /**
     * gets the User entity corresponding to authenticated identity
     * 
     * @param EntityManager $em
     * @return Entity\User
     */
    protected function getAuthenticatedUser(EntityManager $em)
    {
        $dql = 'SELECT u FROM InterpretersOffice\Entity\User u WHERE u.id = :id';
        $id = $this->auth->getIdentity()->id;        
        $query = $em->createQuery($dql)
                ->setParameters(['id'=>$id])
                ->useResultCache(false);
        $user = $query->getOneOrNullResult();
        
        return $user;        
        
    }    
}
