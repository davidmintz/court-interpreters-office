<?php
/**
 *  module/InterpretersOffice/src/Entity/Repository/JudgeRepository.php
 */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;


class JudgeRepository extends EntityRepository {
    
    public function findAll()
    {
        $dql = 'SELECT j FROM InterpretersOffice\Entity\Judge j '
                . 'ORDER BY j.lastname, j.firstname';
        
        return $this->getEntityManager()->createQuery($dql)->getResult();
        
    }
    
}

