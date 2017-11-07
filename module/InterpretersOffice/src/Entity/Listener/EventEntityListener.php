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
     * holds a copy of related entities before update
     * 
     * @var array
     */    
    protected $state_before = [
        'interpreter_ids' => [],
        'defendant_ids'   => [],        
    ];
    
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
     * @return EventEntityListener
     */
    public function setAuth(AuthenticationServiceInterface $auth)
    {
        $this->auth = $auth;
        return $this;
    }
    /**
     * postLoad callback
     *    
     * @param Entity\Event $eventEntity
     * @param LifecycleEventArgs $event
     */
    public function postLoad(Entity\Event $eventEntity, 
         LifecycleEventArgs $event)
    {        
        $this->logger
          ->debug("running ".__FUNCTION__ . " in your EventEntityListener ...");
        // for now, just for kicks:
        $this->getEventManager()->trigger(__FUNCTION__, $this);
        foreach ($eventEntity->getInterpreterEvents() as $interpEvent) {
            $this->state_before['interpreter_ids'][] = 
                    $interpEvent->getInterpreter()->getId();
        }
        foreach ($eventEntity->getDefendantNames() as $defendant) {
            $this->state_before['defendant_ids'][] = 
                    $defendant->getId();
        }
        
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
            PreUpdateEventArgs $args)
    {
        $changeSet = $args->getEntityChangeSet();
        printf('<pre>%s</pre>',print_r(array_keys($changeSet),true));
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
        
        $eventEntity->setCreated($this->now)
                ->setModifiedBy($user)
                ->setModified($this->now);
        foreach ($eventEntity->getInterpreterEvents() as $interpreterEvent) {
           
            $interpreterEvent
                    ->setCreatedBy($user)
                    ->setCreated($this->now);
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
