<?php

/** module/InterpretersOffice/src/Entity/Repository/ResultCachingQueryTrait.php */
declare(strict_types=1);
namespace InterpretersOffice\Entity\Repository;
use Doctrine\Orm\Query;
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
    public function createQuery(string $dql, string $cache_id = null, int $lifetime = 0) : Query
    {
        $query = $this->getEntityManager()->createQuery($dql);
        $query->useResultCache(true, $lifetime, $cache_id);

        return $query;
    }
}
