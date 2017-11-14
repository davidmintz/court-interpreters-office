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
    
    protected $datetime_properties = ['date','time','modified','submission_datetime'];
    
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
       
        foreach ($eventEntity->getInterpreterEvents() as $interpEvent) {
            $this->state_before['interpreter_ids'][] = 
                    $interpEvent->getInterpreter()->getId();
        }
        foreach ($eventEntity->getDefendantNames() as $defendant) {
            $this->state_before['defendant_ids'][] = 
                    $defendant->getId();
        }
        // this approach is not gonna work.
        $this->state_before['modified'] = $eventEntity->getModified();
        $this->state_before['date'] = $eventEntity->getDate();
        $this->state_before['time'] = $eventEntity->getTime();
        $this->state_before['submission_datetime'] = $eventEntity->getSubmissionDatetime();
        // for now, just for kicks:
        $this->getEventManager()->trigger(__FUNCTION__, $this);
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
        
        $modified = false;
        
        $interpreters_before = $this->state_before['interpreter_ids'];
        $interpreters_after = [];
        $interpreterEvents = $eventEntity->getInterpreterEvents();
        foreach ($interpreterEvents as $interpEvent) {
            $interpreters_after[] = 
                    $interpEvent->getInterpreter()->getId();
        }
        if ($interpreters_before != $interpreters_after) {            
             $modified = true;            
             $added = array_diff($interpreters_after,$interpreters_before);
             // client-side-supplied created_by should agree with 
             // currently authenticated user, but new entities can't get 
             // inserted without a created_by id, so we have to check after
             // the fact and correct if necessary (until we come up with a 
             // better plan).
             
             /** @todo factor out into its own function?  */
             $current_user_id = $this->auth->getStorage()->read()->id;
             foreach ($interpreterEvents as $ie) {
                 $creator_id = $ie->getCreatedBy()->getId();
                 if (in_array($ie->getInterpreter()->getId(),$added)) {
                     if ($creator_id != $current_user_id) {
                         $interpreter = $ie->getInterpreter();
                         $this->logger->warn(
                         sprintf(
                           'submitted creator id inconsistent with current user'
                           . ' in event id %d, interpreter id %d (%s)',
                        $eventEntity->getId(),
                        $added, $interpreter->getLastname()),
                        compact('creator_id','current_user_id'));
                     }
                     $ie->setCreatedBy(
                        $this->getAuthenticatedUser($args->getEntityManager()));
                 } 
            }             
        }
        
        $defendants_before = $this->state_before['defendant_ids'];
        $defendants_after = [];
        
        foreach($eventEntity->getDefendantNames() as $deft) {
            $defendants_after[] = $deft->getId();
        }
        if ($defendants_after != $defendants_before) {
            $modified = true;
        }
        /** @todo stop WASTING OUR CPU CYCLES with this 
         * because it does NOT work */
        
        $datetime_props = ['date','time','submission_datetime','modified'];
        $changeSet = $args->getEntityChangeSet();
        $other_props = array_diff(array_keys($changeSet),$datetime_props);
        $fields_modified = array_keys($changeSet);
        // loop through datetime fields and see if before and after are 
        // *equivalent* 
        
        foreach($datetime_props as $prop) {
            if (!in_array($prop,$fields_modified)) {
                continue;
            }
            list($before,$after) = $changeSet[$prop];
            if ('time' == $prop) {
                // compare only the time part of it
                $before = $before->format("H:i:s");
                $after  = $after->format("H:i:s");
            }
            if ($before == $after) {
                $this->logger->debug("$prop has not really been modified, trying to undo");
                // a waste of time, just as we suspected:
                unset($changeSet[$prop]); 
                
                // neither does this work.....
                if ($prop == 'submission_datetime') {
                    $method = 'setSubmissionDateTime';
                } else {
                    $method = 'set' . ucfirst($prop);
                }
                $eventEntity->$method($this->state_before[$prop]);
                $this->logger->debug("ran $method on $prop");
            }
        }
        
        
        if ($modified or count($other_props)) {
            $eventEntity
                    ->setModified($this->now)
                    ->setModifiedBy($this->getAuthenticatedUser($args->getEntityManager()));
            $debug = "real changes detected, updated event meta in with event id ".$eventEntity->getId();
        } else {
            $debug = "no actual update detected with event id ".$eventEntity->getId();
        }
        $this->logger->debug($debug);
       
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
            // for us
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
