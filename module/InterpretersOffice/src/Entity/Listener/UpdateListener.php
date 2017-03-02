<?php
/** 
 * module/InterpretersOffice/src/Entity/Listener/UpdateListener.php
 */
namespace InterpretersOffice\Entity\Listener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping;


/**
 * a start on an entity listener for clearing caches
 */
class UpdateListener {
    
    // temporary constructor argument
    public function __construct($cacheNamespace, $logger)
    {
        $this->logger = $logger;
        $this->namespace = $cacheNamespace;
    }
    /**
	 * 
	 * @return void
	 */
	public function postUpdate(LifecycleEventArgs $args) {
		
        $entity = $args->getObject();
        $this->logger->debug(sprintf(
             '%s happened on entity %s',__METHOD__,get_class($entity)
        ));
        $cache = $args->getObjectManager()->getConfiguration()->getResultCacheImpl();
        $cache->setNamespace($this->namespace);
        $cache->deleteAll();
        $this->logger->debug(sprintf(
             '%s happened on entity %s, cleared cache ',__METHOD__,get_class($entity)
        ));
        //->getConfiguration()->getResultCacheImpl()
            
    }  

    public function postPersist(LifecycleEventArgs $args)
    {
        return $this->postUpdate($args);
    }
}
