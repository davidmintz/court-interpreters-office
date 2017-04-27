<?php

/**  module/InterpretersOffice/src/Entity/Repository/InterpreterRepository.php */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

use Zend\Paginator\Paginator as ZendPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
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
    
    public function search($params,$page = 1)
    {
        if ( isset($params['name'])) {
            return $this->findByName($params);
        }
         //orm:run-dql "SELECT i.lastname FROM InterpretersOffice\Entity\Interpreter i JOIN i.interpreterLanguages il JOIN il.language l WHERE l.name = 'Spanish'"
        //print_r($params);return [];
        $qb = $this->createQueryBuilder('i');
        $qb->select('i.lastname, i.firstname, i.id, i.active, i.securityExpirationDate','h.name AS hat')
            ->join('i.hat','h');
        if ( !empty($params['language_id'])) {
            $qb->join('i.interpreterLanguages', 'il')
                ->join('il.language','l')
                ->where('l.id = :id')
                ->setParameters([':id' => $params['language_id']]);
        }
         
        $adapter = new DoctrineAdapter(new ORMPaginator($qb->getQuery()));
        $paginator = new ZendPaginator($adapter);
        if (! count($paginator)) {
            return null;
        }
         /*
        $adapter = new DoctrineAdapter(new ORMPaginator($qb->getQuery()));
        $paginator = new ZendPaginator($adapter);
        if (! count($paginator)) {
            return null;
        }
        $paginator->setCurrentPageNumber($page)
            ->setItemCountPerPage(40);
        return $paginator;                   
         */
        return  $qb->getQuery()->getResult();
        // echo __FUNCTION__ . " is running ... ";
        // echo  $qb->getDQL();
    }
    
    

}
