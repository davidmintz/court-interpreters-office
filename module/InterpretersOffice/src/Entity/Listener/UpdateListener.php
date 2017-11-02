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
 * a start on an entity listener for clearing caches
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
     * implements EventSubscriber
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return ['postUpdate','postPersist','postRemove','preUpdate','prePersist'];
    }

    public function setAuthenticationService(AuthenticationServiceInterface $auth)
    {
        $this->auth = $auth;
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
     * 
     */
    public function prePersist(LifecycleEventArgs $args)
    {
         $entity = $args->getObject();
         if ($entity instanceof Entity\InterpreterEvent) {
             echo "um, WTF? in ".__METHOD__. "<br>";
             $entity->setCreated(new \DateTime());
             // $em = $args->getObjectManager();
             // $user = $em->find(Entity\User::class,$this->auth->getStorage()->read()->id);
             // $entity->setCreatedBy($user);
             // trying to inject auth object and so as to set createdBy resulted 
             // in functions-nested-over-256-levels error at factory instantiation
             // 
         }
    }
    
    /**
     * 
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
         $entity = $args->getObject();
         if ($entity instanceof Entity\InterpreterEvent) {
             echo "um, these DO NOT GET UPDATED, do they??? in ".__METHOD__. "<br>";
             // to do: inject authenticated User entity, set user, set creation time
         }
    }
}
