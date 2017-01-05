<?php
/** module/InterpretersOffice/src/Entity/Repository/ResultCachingQueryTrait.php */

namespace InterpretersOffice\Entity\Repository;

/**
 * trait for easing creating of queries that use result caching
 */
trait ResultCachingQueryTrait {
    
    /**
     * wrapper for EntityManager::createQuery() that turns on result cache
     * @param string $dql
     * @return Doctrine\Orm\Query
     */
    function createQuery($dql='')
    {
       
        $query = $this->getEntityManager()->createQuery($dql);
        $query->useResultCache(true);
        return $query;

    }
    
}
