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
    public function __construct($logger)
    {
        $this->logger = $logger;
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
            
    }  
}
