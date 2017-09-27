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
     * @var \DateTime
     * protected $now
     */
    
    public function __construct() {
        
        $this->now = new \DateTime();
    }
    
    
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
     * sets Event metadata, e.g., who created the Event and when
     * 
     * @param \InterpretersOffice\Entity\Event $eventEntity
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Entity\Event $eventEntity, LifecycleEventArgs $event)
    {        
        
        if (! $eventEntity->getCreatedBy()) {
            // because in test environment, this might already have been done
            $user = $this->getAuthenticatedUser($event->getEntityManager());
            $eventEntity->setCreatedBy($user);
        } else {
            // so we don't blow up in the test environment
            $user = $eventEntity->getCreatedBy(); 
        }
        $now = new \DateTime();
        $eventEntity->setCreated($this->now)
                ->setModifiedBy(null)
                ->setModified(null);
        foreach ($eventEntity->getInterpretersAssigned() as $interpreterEvent) {
            $interpreterEvent
                    ->setCreatedBy($user)
                    ->setCreated($now);
        }
        $this->logger->debug(__FUNCTION__ . " in EventEntityListener really did shit");
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
                ->useResultCache(true);
        $user = $query->getOneOrNullResult();
        
        return $user;        
        
    }    
}
