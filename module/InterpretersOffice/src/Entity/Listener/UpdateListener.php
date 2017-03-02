<?php
/** 
 * module/InterpretersOffice/src/Entity/Listener/UpdateListener.php
 */
namespace InterpretersOffice\Entity\Listener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping;

use InterpretersOffice\Entity\Repository\CacheDeletionInterface;


/**
 * a start on an entity listener for clearing caches
 */
class UpdateListener {
    
    // temporary constructor argument
    public function __construct($cache_id = null, $logger)
    {
        $this->logger = $logger;
        $this->cache_id = $cache_id;
    }
    
    /**
	 * experimental, soon to be revised
	 * @return void
	 */
	public function postUpdate(LifecycleEventArgs $args) {
		
        $entity = $args->getObject();
        $this->logger->debug(sprintf(
             '%s happened on entity %s',__METHOD__,get_class($entity)
        ));
        $cache = $args->getObjectManager()->getConfiguration()->getResultCacheImpl();
        
        if (isset($this->cache_id)) {
            if ($cache->contains($this->cache_id)) {
                $this->logger->debug("found cache item $this->cache_id");
                $cache->delete($this->cache_id);                
            } else {
                $this->logger->debug("NOT found: cache item $this->cache_id");
            }
            
        } else {
            $repository = $args->getObjectManager()->getRepository(get_class($entity));
            if ($repository instanceof CacheDeletionInterface) {
                $repository->deleteCache();
            }            
        }
        
        $this->logger->debug(sprintf(
             '%s event happened on entity %s, cache id %s',
                __METHOD__,
                get_class($entity),
                $this->cache_id ?: 'n/a'
        ));            
    }  

    public function postPersist(LifecycleEventArgs $args)
    {
        return $this->postUpdate($args);
    }
}
