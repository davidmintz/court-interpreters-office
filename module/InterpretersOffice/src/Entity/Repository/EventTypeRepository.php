<?php
/**
 *  module/InterpretersOffice/src/Entity/Repository/EventTypeRepository.php
 */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * custom repository class for EventType entity 
 * 
 */
class EventTypeRepository extends EntityRepository {
    
    /**
     * gets all the event-types, with sorting
     * 
     * note to self: find out if there's a way to make parent class' findAll()
     * sort for us.
     * 
     * @return array
     */
    public function findAll()
    {
        $dql = 'SELECT t FROM InterpretersOffice\Entity\EventType t ORDER BY t.name';
        
        return $this->getEntityManager()->createQuery($dql)->getResult();
        
    }
    
}
