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
     * constructor.
     *
     * @param EntityManagerInterface              $em
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class
     */
    public function __construct(EntityManagerInterface $em, \Doctrine\ORM\Mapping\ClassMetadata $class)
    {
        parent::__construct($em, $class);
    }
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
     * returns all the courthouses and courtrooms
     * 
     * @return array of Location entities
     */
    public function getJudgeLocations()
    {
        // try this one with the querybuilder just for amusement
        $qb = $this->createQueryBuilder("l")
            ->join('l.type', 't')
            ->leftJoin('l.parentLocation p')
            ->where('t.type IN (:types)')
            ->setParameter('types',['courthouse','courtroom'])
            ->addOrderBy('p.name','DESC')->addOrderBy('l.name','ASC');
        $query = $qb->getQuery();
        return $query->getResult();
    }
}
