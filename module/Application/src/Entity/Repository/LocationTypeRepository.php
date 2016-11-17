<?php
/** module/Application/src/Entity/Repository/LocationTypeRepository.php */

namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * custom EntityRepository class for LocationType entity.
 */
class LocationTypeRepository extends EntityRepository
{
    /**
     * gets all the location types ordered by type ascending.
     * 
     * @return Array of all our LocationType objects
     */
    public function findAll() {
        // have the decency to sort them by name-of-type ascending
        $query = $this->getEntityManager()->createQuery(
            'SELECT t FROM Application\Entity\LocationType t ORDER BY t.type ASC'
        );
        return $query->getResult();
    }

}
