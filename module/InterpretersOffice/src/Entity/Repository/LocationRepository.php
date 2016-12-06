<?php

/** module/InterpretersOffice/src/Entity/LocationRepository.php */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Zend\Paginator\Paginator as ZendPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
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
     * returns all the "parent" locations (those that are not nested in another)
     */
    public function getParentLocations()
    {
        $query = $this->getEntityManager()->createQuery(
        'SELECT l FROM InterpretersOffice\Entity\Location l '
         . 'WHERE l.parentLocation IS NULL ORDER BY l.name ASC'
        );
        return $query->getResult();
    }

    
}
