<?php
/** module/InterpretersOffice/src/Entity/Repository/CacheDeletionInterface.php */
namespace InterpretersOffice\Entity\Repository;

/**
 * Interface for repository classes that know how to clear the cache.
 * 
 * Getting rid of this in favor of a different approach is under consideration.
 * 
 */
interface CacheDeletionInterface {
    
    /**
     * clears the cache
     * 
     * @param string $cache_id
     */
    public function deleteCache($cache_id = null);
    
}
