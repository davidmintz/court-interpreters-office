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
    public function __construct($em, \Doctrine\ORM\Mapping\ClassMetadata $class)
    {

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
            ->addOrderBy('l.name', 'ASC');
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

        $dql = 'SELECT l FROM InterpretersOffice\Entity\Location l '
                .'JOIN l.parentLocation p JOIN l.type t '
                .'WHERE p.id = :parent_id AND t.type = \'courtroom\' ORDER BY l.name ASC';
        $query = $this->getEntityManager()->createQuery($dql, 'locations-courtrooms')
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
     * gets children of parent location $parent_id
     *
     * @param int $parent_id
     * @param int $type_id
     * @return array
     * @todo refactor shit. make getCourtrooms proxy to this?
     */
    public function getChildren($parent_id, $type_id = null)
    {
        $params = [':parent_id' => $parent_id];
        $dql = 'SELECT l, t.type FROM InterpretersOffice\Entity\Location l '
                . 'JOIN l.parentLocation p JOIN l.type t ';
        $where = 'p.id = :parent_id ';
        if ($type_id) {
            $params[':type_id'] = $type_id;
            $where .= 'AND t.id = :type_id ';
        }
        $dql .= " WHERE $where";// ORDER BY t.type, l.name";

        $data = $this->createQuery($dql)
                ->useResultCache(true)
                ->setParameters($params)->getResult();
        usort($data, [$this,'sort']);
        return array_column($data, 0);
    }


    /**
     * sort rows
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    public function sort($a, $b)
    {
        $type_1 = $a['type'];
        $type_2 = $b['type'];
        if ($type_1 == 'courtroom' && $type_2 != 'courtroom') {
            return -1;
        } elseif ($type_1 != 'courtroom' && $type_2 == 'courtroom') {
            return 1;
        }
        if ($type_1 == $type_2) {
            return strnatcasecmp($a[0]->getName(), $b[0]->getName());
        } else {
            return strcasecmp($type_1, $type_2);
        }
    }

    /**
     * gets children of parent location $parent_id for populating select elements
     *
     * @param int $parent_id
     * @return array
     */
    public function getChildLocationValueOptions($parent_id)
    {
        $dql = 'SELECT l.id AS value, l.name AS label, t.type '
                . 'FROM InterpretersOffice\Entity\Location l '
                . 'JOIN l.parentLocation p JOIN l.type t '
                . ' WHERE  p.id = :parent_id ';

         $data = $this->createQuery($dql)
                ->useResultCache(true)
                ->setParameters([':parent_id' => $parent_id])->getResult();

        usort($data, function ($a, $b) {
            // if the types are the same, the label decides
            if ($a['type'] == $b['type']) {
                return strnatcasecmp($a['label'], $b['label']);
            // if either is a courtroom, it wins (b/c the other isn't)
            } elseif ($a['type'] == 'courtroom') {
                return -1;
            } elseif ($b['type'] == 'courtroom') {
                return 1;
            }
            return strnatcasecmp($a['type'], $b['type']);
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
            'SELECT l.id, l.name, p.name AS parent '
            . 'FROM InterpretersOffice\Entity\Location l '
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
    public function deleteCache($cache_id = null)
    {

         $this->cache->setNamespace('locations');
         $this->cache->deleteAll();
         // for debugging
         return sprintf('ran %s at line %d', __METHOD__, __LINE__);
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

     /**
      * does entity $id have related entities?
      *
      * returns false if this Location has no related
      * entities and can therefore safely be deleted
      * @param int $id entity id
      * @return boolean true if there are related entities
      */
     public function hasRelatedEntities($id)
     {

         $dql = 'SELECT COUNT(e.id)  +  COUNT(c.id)  + COUNT(j.id)
         FROM InterpretersOffice\Entity\Location l LEFT JOIN l.events e
         LEFT JOIN l.childLocations c LEFT JOIN l.judges j WHERE l.id = :id';
         return $this->getEntityManager()->createQuery($dql)->setParameters(
             ['id'=>$id])->getSingleScalarResult() ? true : false;
     }
}
