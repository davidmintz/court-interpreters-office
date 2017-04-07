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
     * @param string $cache_id
     * @param int $lifetime
     *
     * @return Doctrine\Orm\Query
     */
    public function createQuery($dql, $cache_id = null, $lifetime = 0)
    {
        $query = $this->getEntityManager()->createQuery($dql);
        $query->useResultCache(true, $lifetime, $cache_id);

        return $query;
    }
}
