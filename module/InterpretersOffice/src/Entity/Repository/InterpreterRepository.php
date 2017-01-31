<?php 
/**  module/InterpretersOffice/src/Entity/Repository/InterpreterRepository.php */


namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * custom repository class for EventType entity.
 */
class InterpreterRepository extends EntityRepository
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
        $em->getConfiguration()->getResultCacheImpl()->setNamespace('interpreters');
        parent::__construct($em, $class);
    }

    
 }

