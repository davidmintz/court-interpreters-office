<?php

/** module/InterpretersOffice/src/Entity/Repository/LocationTypeRepository.php */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * custom EntityRepository class for LocationType entity.
 */
class LocationTypeRepository extends EntityRepository implements CacheDeletionInterface
{


    use ResultCachingQueryTrait;

    /**
     * constructor
     *
     * @param \Doctrine\ORM\EntityManager  $em    The EntityManager to use.
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class The class descriptor.
     */

    public function __construct($em, \Doctrine\ORM\Mapping\ClassMetadata $class)
    {

        parent::__construct($em, $class);
        $this->cache = $em->getConfiguration()->getResultCacheImpl();
        $this->cache->setNamespace('locations');
    }
    /**
     * cache
     *
     * @var CacheProvider $cache
     */
    protected $cache;


    /**
     * gets all the location types ordered by type ascending.
     *
     * @return array of all our LocationType objects
     */
    public function findAll()
    {
        // have the decency to sort them by name-of-type, ascending
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
    public function deleteCache($cache_id = null)
    {

         $this->cache->setNamespace('locations');
         $this->cache->deleteAll();
         // tmp
         return sprintf('ran %s at line %d', __METHOD__, __LINE__);
    }
}
