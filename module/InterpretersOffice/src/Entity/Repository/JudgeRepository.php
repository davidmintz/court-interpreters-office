<?php
/**
 *  module/InterpretersOffice/src/Entity/Repository/JudgeRepository.php
 */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * custom repository class for EventType entity 
 * 
 */
class JudgeRepository extends EntityRepository {
    
    /**
     * gets all the Judge entities, sorted
     * 
     * @return array
     */
    public function findAll()
    {
        $dql = 'SELECT j FROM InterpretersOffice\Entity\Judge j '
                . 'ORDER BY j.lastname, j.firstname';
        
        return $this->getEntityManager()->createQuery($dql)->getResult();
        
    }
    
}

