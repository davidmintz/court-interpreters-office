<?php

/** module/InterpretersOffice/src/Entity/LocationRepository.php */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * custom EntityRepository class for Location entity.
 */
class LocationRepository extends EntityRepository
{

    use ResultCachingQueryTrait;

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
     * gets all the locations of type 'courthouse'
     *
     * useful for populating the location form in the context
     * of the "add judge" form
     *
     * @return array
     */
    public function getCourthouses()
    {
        $qb = $this->createQueryBuilder("l")
            ->join('l.type', 't')
            ->where('t.type = :type')
            ->setParameter('type', 'courthouse')
            ->addOrderBy('l.name', 'ASC');//->addOrderBy('l.name','ASC');
        $query = $qb->getQuery()->useResultCache(true);
        
        return $query->getResult();
        
    }
    /**
     * gets data for courtroom SELECT element options
     * 
     * @param int $parent_id id of parent courthouse
     * @return array
     */
    public function getCourtroomValueOptions($parent_id)
    {
        $dql = 'SELECT l.id, l.name  FROM InterpretersOffice\Entity\Location l '
                . 'JOIN l.parentLocation p JOIN l.type t '
                . 'WHERE p.id = :parent_id AND t.type = \'courtroom\'';
        $query = $this->createQuery($dql)
                ->setParameter('parent_id', $parent_id);
        $data = $query->getResult();
        usort( $data, function($a,$b) { return strnatcasecmp ($a['name'],$b['name'] );});
        return $data;
        
    }
    /**
     * gets all the courtrooms whose parent courthouse is $parent_id
     *
     * @param int $parent_id
     * @return array $options
     */
    public function getCourtrooms($parent_id)
    {
        if (! $parent_id) { return []; }
        if (isset($options['json']) && $options['json']) {
            $what = 'l.id, l.name';
        } else {
            $what = 'l';
        }
        $dql = 'SELECT '.$what.' FROM InterpretersOffice\Entity\Location l '
                . 'JOIN l.parentLocation p JOIN l.type t '
                . 'WHERE p.id = :parent_id AND t.type = \'courtroom\' ORDER BY l.name ASC';
        $query = $this->getEntityManager()->createQuery($dql)
                ->setParameter('parent_id', $parent_id)->useResultCache(true);
        $data = $query->getResult();
        // maybe it will run faster if you cram it into one line
        usort($data,function($a,$b){return strnatcasecmp($a->getName(), $b->getName());});
        return $data;
    }
    /**
     * returns all the courthouses and courtrooms
     *
     * @return array of Location entities
     */
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
}
