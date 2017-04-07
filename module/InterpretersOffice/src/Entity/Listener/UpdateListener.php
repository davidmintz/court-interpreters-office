<?php
/**
 * module/InterpretersOffice/src/Entity/Listener/UpdateListener.php
 */
namespace InterpretersOffice\Entity\Listener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber;
use InterpretersOffice\Entity\Repository\CacheDeletionInterface;

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
        return ['postUpdate','postPersist','postRemove'];
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
            $status = $repository->deleteCache();
            $this->logger->debug("called delete cache on ".get_class($repository));
            $this->logger->debug("$status");
        } else {
            $this->logger->debug("! not an implementation of CacheDeletionInterface:    ".get_class($repository));
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
}
