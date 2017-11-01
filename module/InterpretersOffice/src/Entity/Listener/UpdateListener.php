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

/**
 * a start on an entity listener for clearing caches
 */
class UpdateListener implements EventSubscriber, Log\LoggerAwareInterface
{

    use Log\LoggerAwareTrait;

    /**
     * implements EventSubscriber
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return ['postUpdate','postPersist','postRemove','preUpdate','prePersist'];
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
             echo "um, FUCK YOU? in ".__METHOD__. "<br>";
             // to do: inject authenticated User entity, set user, set creation time
         }
    }
    
    /**
     * 
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
         $entity = $args->getObject();
         if ($entity instanceof Entity\InterpreterEvent) {
             echo "um, FUCK YOU? in ".__METHOD__. "<br>";
             // to do: inject authenticated User entity, set user, set creation time
         }
    }
}
