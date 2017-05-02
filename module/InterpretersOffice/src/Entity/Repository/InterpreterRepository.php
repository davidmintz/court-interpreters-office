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
        if ( !empty($params['name'])) {
            return $this->findByName($params);
        }
        //orm:run-dql "SELECT i.lastname FROM InterpretersOffice\Entity\Interpreter i JOIN i.interpreterLanguages il JOIN il.language l WHERE l.name = 'Spanish'"

        $qb = $this->createQueryBuilder('i');
        $queryParams = [];
        
        //https://github.com/doctrine/doctrine2/issues/2596#issuecomment-162359725
        $qb->select('PARTIAL i.{lastname, firstname, id, active, securityExpirationDate}','h.name AS hat')
            ->join('i.hat','h');
        
        // keep track of whether we have needed to set any WHERE clauses
        $hasWhereConditions = false;
        
        // are they filtering for active|inactive?
        switch  ($params['active']) {
        case -1;
            $active_clause = '';
            break;
        case 0;
            $active_clause = 'i.active = false';
            break;
        case 1:
            $active_clause = 'i.active = true';           
            break;
        }
        if ($active_clause) {
            $qb->where($active_clause);
            $hasWhereConditions = true;
        }
        // are they filtering for language?
        if ( !empty($params['language_id'])) {
            $method = $hasWhereConditions ? 'andWhere' : 'where';
            $qb->join('i.interpreterLanguages', 'il')
                ->join('il.language','l')
                ->$method('l.id = :id');
            $queryParams[':id'] = $params['language_id'];
        }
        
        // are they filtering for security clearance?
        switch ($params['security_clearance_expiration']) {
            
        case -1; // any status whatsoever
            $security_expiration_clause = '';
            break;
        case 0;  // expired
            $security_expiration_clause = 'i.securityExpirationDate < :today ';
            $queryParams[':today'] = new \DateTime();
            $hasWhereConditions = true;
            break;
        case 1; // valid
            $security_expiration_clause = 'i.securityExpirationDate >= :today ';
            $queryParams[':today'] = new \DateTime();
            $hasWhereConditions = true;
            break;
        case -2; // NULL
            $security_expiration_clause = 'i.securityExpirationDate IS NULL';
            $hasWhereConditions = true;
            break;
        }
        if ($security_expiration_clause) {
            $method = $hasWhereConditions ? 'andWhere' : 'where';
            $qb->$method($security_expiration_clause);
        }
        
        
        if ($queryParams) { 
            $qb->setParameters($queryParams);
        }
        $adapter = new DoctrineAdapter(new ORMPaginator($qb->getQuery()));
        
        $paginator = new ZendPaginator($adapter);
        echo $qb->getDQL(); 
        $found = $paginator->count();
        if (! $found) {
            
            return null;
        }
        echo "<br>".__METHOD__. " found $found ...";
        $paginator
            ->setCurrentPageNumber($page)
            ->setItemCountPerPage(40);
        return $paginator;                   
        
    }
}
