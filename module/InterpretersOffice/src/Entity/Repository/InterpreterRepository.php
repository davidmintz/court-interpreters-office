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

        $qb = $this->createQueryBuilder('i');
        $queryParams = [];
        
        //https://github.com/doctrine/doctrine2/issues/2596#issuecomment-162359725
        $qb->select('PARTIAL i.{lastname, firstname, id, active, securityExpirationDate}','h.name AS hat')
            ->join('i.hat','h');
        
        $active_clause = '';
        $hasWhere = false;
        switch  ($params['active']) {
        case -1;
            break;
        case 0;
            $active_clause = 'i.active = false';
            $hasWhere = true;
            break;
        case 1:
            $active_clause = 'i.active = true';
            $hasWhere = true;
            break;
        }
        if ($active_clause) {
            $qb->where($active_clause);
        }
        
        if ( !empty($params['language_id'])) {
            $method = $hasWhere ? 'andWhere' : 'where';
            $qb->join('i.interpreterLanguages', 'il')
                ->join('il.language','l')
                ->$method('l.id = :id');
            $queryParams[':id'] = $params['language_id'];
        }
        $security_expiration_clause = '';
        switch ($params['security_clearance_expiration']) {
        case -1;
            break;
        case 0;
            break;
        case 1;
            break;
        case 2;
            break;

        }
        /* TO DO 
            1 => 'valid',
             0 => 'expired',
            -2 => 'none',
            -1 => 'any status',
            
        */
          
        if ($queryParams) { 
            $qb->setParameters($queryParams);
        }
        $adapter = new DoctrineAdapter(new ORMPaginator($qb->getQuery()));
        
        $paginator = new ZendPaginator($adapter);
        
        if (! $paginator->count()) {
            return null;
        }
        echo $qb->getDQL();
        $paginator
            ->setCurrentPageNumber($page)
            ->setItemCountPerPage(40);
        return $paginator;                   
        
    }
}
