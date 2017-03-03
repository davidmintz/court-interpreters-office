<?php

/** module/InterpretersOffice/src/Entity/Repository/LocationTypeRepository.php */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * custom EntityRepository class for LocationType entity.
 */
class LocationTypeRepository extends EntityRepository implements CacheDeletionInterface
{
    /**
     * experimental
     * 
     * @var array
     */
    protected $cache_ids = [
        'location-types',
        'location-types-with-totals',
        'judge-location-types',
    ];
    /**
     * gets all the location types ordered by type ascending.
     *
     * @return array of all our LocationType objects
     */
    public function findAll()
    {
        // have the decency to sort them by name-of-type ascending
        $query = $this->getEntityManager()->createQuery(
            'SELECT t FROM InterpretersOffice\Entity\LocationType t ORDER BY t.type ASC'
        )->useResultCache(true, null, 'location-types');

        return $query->getResult();
    }

    /**
     * returns all the location types with total number of each.
     *
     * @return array
     */
    public function findAllWithTotals()
    {
        $dql = 'SELECT t.id, t.type, COUNT(l.id) AS total 
                FROM InterpretersOffice\Entity\LocationType t 
                LEFT JOIN t.locations l GROUP BY t.type ORDER BY t.type';

        return $this->getEntityManager()->createQuery($dql)
                ->useResultCache(true, null, 'location-types-with-totals')
                ->getResult();
    }

    /**
     * returns courthouse and courtroom location types.
     *
     * @return array of LocationType entities
     */
    public function getJudgeLocationsTypes()
    {
        $dql = 'SELECT t FROM InterpretersOffice\Entity\LocationType t WHERE t. type IN (:types) '
                .'ORDER BY t.type ASC';
        $query = $this->getEntityManager()->createQuery($dql)
                ->setParameters([':types' => ['courtroom', 'courthouse']])
                ->useResultCache(true, null, 'judge-location-types');

        return $query->getResult();
    }
    
    /**
     * experimental 
     * 
     * implements cache deletion
     * @param type $cache_id
     */
    public function deleteCache($cache_id = null) {
        $cache = $this->getEntityManager()->getConfiguration()->getResultCacheImpl();
        if ($cache_id) {
            $cache->delete($cache_id);
        } else {
            foreach ($this->cache_ids as $cache_id) {
                $cache->delete($cache_id);
            }
        }
        $other_repo = $this->getEntityManager()->getRepository('InterpretersOffice\Entity\Location');
        $status = $other_repo->deleteCache();
        return $status;
    }
}
