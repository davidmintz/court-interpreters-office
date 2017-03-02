<?php

/** module/InterpretersOffice/src/Entity/Repository/ResultCachingQueryTrait.php */

namespace InterpretersOffice\Entity\Repository;

/**
 * trait for easing creating of queries that use result caching.
 */
trait ResultCachingQueryTrait 
{
    /**
     * wrapper for EntityManager::createQuery() that turns on result cache.
     *
     * @param string $dql
     *
     * @return Doctrine\Orm\Query
     */
    public function createQuery($dql,$cache_id = null)
    {
        $query = $this->getEntityManager()->createQuery($dql);
        if (! $cache_id) {
            $cache_id = $this->getCacheId();
        }
        $query->useResultCache(true,7200,$cache_id);

        return $query;
    }
    
    public function getCacheId()
    {
        if (isset($this->cache_id)) {
            return $this->cache_id;
        } else {
            /** @todo  compute it ourself and save in $this->cache_id */
            // strtolower((new \Zend\Filter\Word\CamelCaseToDash)->filter());
        }
        
    }
}
