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

    /**
     * returns all the "parent" locations (those that are not nested in another).
     *
     * @return array
     */
    public function getParentLocations()
    {
        $query = $this->getEntityManager()->createQuery(
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
        $query = $qb->getQuery();
        return $query->getResult();
    }

    /**
     * gets all the courtrooms whose parent is $parent_id
     *
     * for using xhr to repopulate context-sensitive location dropdowns     * 
     * @todo support an option indexBy parameter for initializing a select 
     * element server-side
     *
     * @param int $parent_id
     * @return array
     */
    public function getCourtrooms($parent_id)
    {
        $dql = 'SELECT l.name,l.id FROM InterpretersOffice\Entity\Location l JOIN l.parentLocation p JOIN l.type t '
                . 'WHERE p.id = :parent_id AND t.type = \'courtroom\' ORDER BY l.name ASC';
        $query = $this->getEntityManager()->createQuery($dql)
                ->setParameter('parent_id', $parent_id);

        return $query->getResult();
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
        $query = $qb->getQuery();
        return $query->getResult();
    }
}
