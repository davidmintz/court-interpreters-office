<?php
/**
 *  module/InterpretersOffice/src/Entity/Repository/EventTypeRepository.php
 */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;


class EventTypeRepository extends EntityRepository {
    
    public function findAll()
    {
        $dql = 'SELECT t FROM InterpretersOffice\Entity\EventType t ORDER BY t.name';
        
        return $this->getEntityManager()->createQuery($dql)->getResult();
        
    }
    
}
