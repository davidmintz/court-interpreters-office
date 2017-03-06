<?php

/** module/InterpretersOffice/src/Entity/LocationRepository.php */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Doctrine\Common\Cache\CacheProvider;

/**
 * custom EntityRepository class for Location entity.
 * 
 * An Doctrine event listener is attached to the update, create and delete 
 * events and calls deleteCache on this class as needed.
 * @todo consider taking out this CacheDeletionInterface stuff and doing this
 * directly in the event listener itself. In that case we won't need to override
 * the constructor either.
 * 
 */
class LocationRepository extends EntityRepository implements CacheDeletionInterface
{
    use ResultCachingQueryTrait;
    
    /**
     * cache
     * 
     * @var CacheProvider 
     */
    protected $cache;
    
    /**
     * constructor
     * 
     * @param \Doctrine\ORM\EntityManager  $em    The EntityManager to use.
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class The class descriptor.
     */
    public function __construct($em, \Doctrine\ORM\Mapping\ClassMetadata $class) {
        
        parent::__construct($em, $class);
        $this->cache = $em->getConfiguration()->getResultCacheImpl();
        $this->cache->setNamespace('locations');
        
    }
   
    /**
     * returns all the "parent" locations (those that are not nested in another).
     *
     * @return array
     */
    public function getParentLocations()
    {
        $query = $this->createQuery(
            'SELECT l FROM InterpretersOffice\Entity\Location l '
            .'WHERE l.parentLocation IS NULL ORDER BY l.name ASC'
            
        );

        return $query->getResult();
    }
    
    /**
     * gets all the locations of type 'courthouse'.
     *
     * useful for populating the location form in the context
     * of the "add judge" form
     *
     * @return array
     */
    public function getCourthouses()
    {
        $qb = $this->createQueryBuilder('l')
            ->join('l.type', 't')
            ->where('t.type = :type')
            ->setParameter('type', 'courthouse')
            ->addOrderBy('l.name', 'ASC'); //->addOrderBy('l.name','ASC');
        $query = $qb->getQuery()->useResultCache(true);

        return $query->getResult();
    }
    
    /**
     * gets data for courtroom SELECT element options.
     *
     * @param int $parent_id id of parent courthouse
     *
     * @return array
     */
    public function getCourtroomValueOptions($parent_id)
    {
        $dql = 'SELECT l.id, l.name  FROM InterpretersOffice\Entity\Location l '
                .'JOIN l.parentLocation p JOIN l.type t '
                .'WHERE p.id = :parent_id AND t.type = \'courtroom\'';
        $query = $this->createQuery($dql)
                ->setParameter('parent_id', $parent_id);
        $data = $query->getResult();
        usort($data, function ($a, $b) {
            return strnatcasecmp($a['name'], $b['name']);
        });

        return $data;
    }
    
    /**
     * gets all the courtrooms whose parent courthouse is $parent_id.
     *
     * @param int $parent_id
     *
     * @return array $options
     */
    public function getCourtrooms($parent_id)
    {
        if (!$parent_id) {
            return [];
        }
        $dql = 'SELECT l FROM InterpretersOffice\Entity\Location l '
                .'JOIN l.parentLocation p JOIN l.type t '
                .'WHERE p.id = :parent_id AND t.type = \'courtroom\' ORDER BY l.name ASC';
        $query = $this->getEntityManager()->createQuery($dql,'locations-courtrooms')
                ->setParameter('parent_id', $parent_id)
                ->useResultCache(true);
        $data = $query->getResult();
        // maybe it would run faster if we crammed it into one line :-)
        usort($data, function ($a, $b) {
            return strnatcasecmp($a->getName(), $b->getName());
        });

        return $data;
    }

    /**
     * find all locations of type id $type_id.
     *
     * @param int $type_id
     *
     * @return array
     */
    public function findByTypeId($type_id)
    {
        $query = $this->createQuery(
            'SELECT l.id, l.name, p.name AS parent FROM InterpretersOffice\Entity\Location l '
                .' LEFT JOIN l.parentLocation p JOIN l.type t '
                .' WHERE t.id = :type_id'
        )->setParameters([':type_id' => $type_id]);
        $data = $query->useResultCache(true)->getResult();
        usort($data, function ($a, $b) {
            return strnatcasecmp("$a[name], $a[parent]", "$b[name], $b[parent]");
        });

        return $data;
    }
    
    /**
     * experimental 
     * 
     * implements cache deletion
     * @param type $cache_id
     */
    public function deleteCache($cache_id = null) {
        
         $this->cache->setNamespace('locations');
         $this->cache->deleteAll();
         // for debugging
         return sprintf('ran %s at line %d',__METHOD__,__LINE__);
        
    }
    /*
     * NOT used and slated for removal
     *
     * returns all the courthouses and courtrooms
     *
     * @return array of Location entities

    public function getJudgeLocations()
    {
        // try this one with the querybuilder just for amusement
        $qb = $this->createQueryBuilder("l")
            ->join('l.type', 't')
            ->leftJoin('l.parentLocation', 'p')
            ->where('t.type IN (:types)')
            ->setParameter('types', ['courthouse','courtroom'])
            ->addOrderBy('p.name', 'DESC')->addOrderBy('l.name', 'ASC');
        $query = $qb->getQuery()->useResultCache(true);
        return $query->getResult();
    }

     */
}
