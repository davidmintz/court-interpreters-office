<?php

/** module/InterpretersOffice/src/Entity/CourtClosingRepository.php */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Zend\Paginator\Paginator as ZendPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Entity\CourtClosing;

/**
 * custom EntityRepository class for CourtClosing entity.
 */
class CourtClosingRepository extends EntityRepository
{

     protected $cache_namespace = 'court-closings';

     /**
      * constructor
      *
      * @param \Doctrine\ORM\EntityManager  $em    The EntityManager to use.
      * @param \Doctrine\ORM\Mapping\ClassMetadata $class The class descriptor.
      */
     public function __construct($em, \Doctrine\ORM\Mapping\ClassMetadata $class)
     {
         parent::__construct($em, $class);
         $this->cache = $em->getConfiguration()->getResultCacheImpl();
         $this->cache->setNamespace($this->cache_namespace);
     }

     /**
      * returns a list of court closings -- WORK IN PROGRESS
      * @param  int $year optional year
      * @return ZendPaginator
      */
     public function list($year = null)
     {
         if (! $year) { $year = date('Y'); }
         $DQL = 'SELECT c FROM '.CourtClosing::class .
         ' c WHERE c.date BETWEEN :from AND :until ORDER BY c.date ASC';
         $query = $this->getEntityManager()->createQuery($DQL)
            ->setParameters([
                'from'=>new \DateTime("$year-01-01"),
                'until'=>new \DateTime("$year-12-31")
            ]);
        return $query->getResult();

     }

}
