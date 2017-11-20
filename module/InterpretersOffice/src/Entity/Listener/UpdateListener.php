<?php
/**
 * module/InterpretersOffice/src/Entity/Listener/UpdateListener.php
 */
namespace InterpretersOffice\Entity\Listener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber;
use InterpretersOffice\Entity\Repository\CacheDeletionInterface;

use InterpretersOffice\Entity;

use Zend\Log;
use Zend\Authentication\AuthenticationServiceInterface;

/**
 * entity listener for clearing caches etc
 */
class UpdateListener implements EventSubscriber, Log\LoggerAwareInterface
{

    use Log\LoggerAwareTrait;
    
    /**
     *
     * @var AuthenticationServiceInterface
     */
    protected $auth;
    
    /**
     * the Event entity
     * 
     * a reference to the current Entity\Event so we can set its metadata
     * on listeners that observe events in related entities.
     * 
     * @var Entity\Event
     */
    protected $eventEntity;
    
    /**
     * current datetime
     * 
     * @var \DateTime
     */
    protected $now;
    
    protected function getTimeStamp()
    {
        if (! $this->now) {
            $this->now = new \DateTime();
        }
        return $this->now;
    }
    
    /**
     * implements EventSubscriber
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return ['postLoad','postUpdate','postPersist','postRemove','preUpdate','prePersist','preRemove'];
    }
    
    /**
     * postLoad handler
     * 
     * keeps a reference to the Event entity in order to update 
     * its meta later on in listeners that observe related entities
     * 
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof Entity\Event) {
            $this->eventEntity = $entity;            
        }
    }
    
    
    
    /**
     * sets the AuthenticationService
     * 
     * @param AuthenticationServiceInterface $auth
     * @return \InterpretersOffice\Entity\Listener\UpdateListener
     */
    public function setAuthenticationService(AuthenticationServiceInterface $auth)
    {
        $this->auth = $auth;
        
        return $this;
    }
    /**
     * postUpdate handler
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function postUpdate(LifecycleEventArgs $args)
    {

        $entity = $args->getObject();
        $this->logger->debug(sprintf(
            '%s happening on entity %s',
            __METHOD__,
            get_class($entity)
        ));
        $repository = $args->getObjectManager()->getRepository(get_class($entity));
        if ($repository instanceof CacheDeletionInterface) {
            $repository->deleteCache();
            $this->logger->debug("called delete cache on ".get_class($repository));
        } else {
            $this->logger->debug("! not an implementation of CacheDeletionInterface:    ".get_class($repository));
        }
        if ($entity instanceof Entity\User) {
            // delete the cache entry
            $cache = $args->getObjectManager()->getConfiguration()->getResultCacheImpl();
            $cache->setNamespace('users');
            $id = $entity->getId();
            if ($cache->contains($id)) {
                $cache->delete($id);
                $this->logger->debug(sprintf("%s in UpdateListener deleted user id $id from cache", __FUNCTION__));
            } else {
                $this->logger->debug(sprintf('%s in UpdateListener: looks like users cache has no %d', __FUNCTION__, $id));
            }
        }
        if ($entity instanceof Entity\InterpreterLanguage) {
            $cache = $args->getObjectManager()->getConfiguration()->getResultCacheImpl();
            $cache->setNamespace('languages');
            $cache->deleteAll();
            $this->logger->debug("InterpreterLanguage entity updated; language cache was purged.");
        }
    }

    /**
     * postPersist event handler
     *
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        return $this->postUpdate($args);
    }

    /**
     * postRemove event handler
     *
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        return $this->postUpdate($args);
    }
    
    /**
     * prePersist
     * 
     * 
     * @todo get rid of this or put to some real use
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        return; 
        
        $entity = $args->getObject();
         if ($entity instanceof Entity\InterpreterEvent) {
             
             $this->logger->debug(sprintf("running %s on InterpreterEvent",__FUNCTION__));             
             $entity->setCreated($this->getTimeStamp());
             if (is_null($entity->getCreatedBy())) {
                 // to do: factor this out into a getter method 
                 // that returns User entity
                 $em = $args->getObjectManager();
                 $user = $em->find(Entity\User::class,$this->auth->getStorage()->read()->id);
                 $entity->setCreatedBy($user);
                 $this->logger->debug(sprintf("set createdBy for InterpreterEvent in %s",__FUNCTION__));
             }
             // $em = $args->getObjectManager();
             // $user = $em->find(Entity\User::class,$this->auth->getStorage()->read()->id);
             // $entity->setCreatedBy($user);
             // trying to inject auth object so as to set createdBy resulted 
             // in functions-nested-over-256-levels error at factory instantiation
             
             
         }
    }
    
    /**
     * 
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
         $entity = $args->getObject();
         if ($entity instanceof Entity\InterpreterEvent) {
             echo "um, these do NOT GET UPDATED, do they??? in ".__METHOD__. "<br>";
             // to do: inject authenticated User entity, set user, set creation time
         }
        
    }
    
    /**
     * preRemove
     * 
     * @todo use it or lose it
     */
    public function preRemove(LifecycleEventArgs $args)
    {
         $entity = $args->getObject();
         if ($entity instanceof Entity\InterpreterEvent) {
            //echo "how now! shit is being removed! ....";
            if ($this->eventEntity) {
                $comments = $this->eventEntity->getComments();
                $this->eventEntity->setComments($comments . "\nho shit! preRemove() callback workeds\n");
            }
         }
         
    }
}
