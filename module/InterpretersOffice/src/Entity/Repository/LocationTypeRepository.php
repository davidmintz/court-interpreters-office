<?php

/** module/InterpretersOffice/src/Entity/Repository/LocationTypeRepository.php */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * custom EntityRepository class for LocationType entity.
 */
class LocationTypeRepository extends EntityRepository
{
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
        );

        return $query->getResult();
    }

    /**
     * returns all the location types with total number of each.
     *
     * @return array
     */
    public function findAllWithTotals()
    {
        $dql = 'SELECT t.id, t.type, COUNT(l.id) AS total FROM InterpretersOffice\Entity\LocationType t 
                LEFT JOIN t.locations l GROUP BY t.type ORDER BY t.type';

        return $this->getEntityManager()->createQuery($dql)->getResult();
    }
    
    /**
     * returns courthouse and courtroom location types
     * 
     * @return array of LocationType entities
     */
    public function getJudgeLocationsTypes()
    {
        $dql = 'SELECT t FROM InterpretersOffice\Entity\LocationType t WHERE t. type IN (:types) '
                . 'ORDER BY t.type ASC';
        $query = $this->getEntityManager()->createQuery($dql)
                ->setParameters([':types'=>['courtroom','courthouse']]);
        return $query->getResult();
    }
}
