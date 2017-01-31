<?php
/**
 *  module/InterpretersOffice/src/Entity/Repository/JudgeRepository.php.
 */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * custom repository class for EventType entity.
 */
class JudgeRepository extends EntityRepository
{
    

    use ResultCachingQueryTrait;

    /**
     * constructor.
     *
     * @param EntityManagerInterface              $em
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class
     */
    public function __construct(EntityManagerInterface $em, \Doctrine\ORM\Mapping\ClassMetadata $class)
    {
        $em->getConfiguration()->getResultCacheImpl()->setNamespace('judges');
        parent::__construct($em, $class);
    }

    /**
     * gets all the Judge entities, sorted.
     *
     * @return array
     */
    public function findAll()
    {
        $dql = 'SELECT j FROM InterpretersOffice\Entity\Judge j '
               .'ORDER BY j.lastname, j.firstname';

        return $this->createQuery($dql)                
                ->getResult();
    }
}
