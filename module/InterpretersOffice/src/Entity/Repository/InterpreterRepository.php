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

    /**
     * looks up Interpreters by name
     *
     * @param string $name
     */
    public function findByName($name)
    {

        echo __FUNCTION__ . " is running ... ";

    }
    
    public function search($params)
    {
        if (! isset($params['name'])) {
            return $this->findByLanguage($params);
        }
    }
    
    /**
     * looks up Interpreters by language
     *
     * @param int $language_id
     * @param Array $params
     * @return Array;
     */
    public function findByLanguage($params)
    {
    //orm:run-dql "SELECT i.lastname FROM InterpretersOffice\Entity\Interpreter i JOIN i.interpreterLanguages il JOIN il.language l WHERE l.name = 'Spanish'"
        //print_r($params);return [];
        $qb = $this->createQueryBuilder('i');
        $qb->select('i.lastname, i.firstname, i.id, i.active, i.securityExpirationDate','h.name AS hat')
           ->join('i.interpreterLanguages', 'il')
           ->join('il.language','l')
           ->join('i.hat','h')
           ->where('l.id = :id')
           ->setParameters([':id' => $params['language_id']]);
        
        return  $qb->getQuery()->getResult();
        // echo __FUNCTION__ . " is running ... ";
        // echo  $qb->getDQL();

    }

}
