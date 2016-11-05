<?php

namespace Application\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

use Zend\Paginator\Paginator as ZendPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;

class LanguageRepository extends EntityRepository
{

	public function __construct($em, \Doctrine\ORM\Mapping\ClassMetadata $class)
    {
        parent::__construct($em,$class);
        
        //$cache = $em->getConfiguration()->getResultCacheImpl();
        //$cache->setNamespace('requests');	
        //$this->cache = $cache; 
    }

    public function findAll($page = 1) 
    {

    	$dql = 'SELECT language FROM Application\Entity\Language language ORDER BY language.name';
    	$query = $this->getEntityManager()->createQuery($dql)->setMaxResults(30);

    	$adapter = new DoctrineAdapter( new ORMPaginator($query)  );
		$paginator = new ZendPaginator($adapter);
		if (! count($paginator)) { return null; }
		$paginator->setCurrentPageNumber($page)
			->setItemCountPerPage(30);
		return $paginator;
		//$defendants = $this->getDefendantsForRequests($paginator);
		//return ['requests'=>$paginator, 'defendants' => $defendants ];
	



    }


}