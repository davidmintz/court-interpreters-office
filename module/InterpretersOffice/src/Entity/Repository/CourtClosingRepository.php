<?php

/** module/InterpretersOffice/src/Entity/CourtClosingRepository.php */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Zend\Paginator\Paginator as ZendPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Entity\CourtClosing;
use InterpretersOffice\Entity\Repository\CacheDeletionInterface;

/**
 * custom EntityRepository class for CourtClosing entity.
 */
class CourtClosingRepository extends EntityRepository implements CacheDeletionInterface
{

    use ResultCachingQueryTrait;

     protected $cache_namespace = 'court-closings';

     /**
      * cache
      *
      * @var CacheProvider
      */
     protected $cache;

     /**
      * constructor
      *
      * @param \Doctrine\ORM\EntityManager  $em    The EntityManager to use.
      * @param \Doctrine\ORM\Mapping\ClassMetadata $class The class descriptor.
      */
     public function __construct($em, \Doctrine\ORM\Mapping\ClassMetadata $class)
     {
         parent::__construct($em, $class);
         $config = $em->getConfiguration();
         $config->addCustomDatetimeFunction('YEAR',
             'DoctrineExtensions\Query\Mysql\Year');
         $this->cache = $config->getResultCacheImpl();
         $this->cache->setNamespace($this->cache_namespace);
     }

     /**
      * implements CacheDeletionInterface
      *
      * @param string $cache_namespace
      * @return boolean
      */
     public function deleteCache($cache_id = null)
     {
         $this->cache->setNamespace($this->cache_namespace);
         return $this->cache->deleteAll();

     }
     /**
      * returns a list of court closings -- WORK IN PROGRESS
      * @param  int $year optional year
      * @return Array
      */
     public function list($year = null)
     {
         if (! $year) { return $this->index(); }
         $DQL = 'SELECT c, h FROM '.CourtClosing::class . ' c
          LEFT JOIN c.holiday h WHERE YEAR(c.date) = :year
          ORDER BY c.date ASC';
         $query = $this->createQuery($DQL)
            ->setParameters(['year'=>$year]);

        return $query->getArrayResult();

     }

     public function index()
     {

         /* @var Doctrine\ORM\QueryBuilder $qb */
        //$qb = $this->getEntityManager()->createQueryBuilder();
        //$qb->select(['year'])
        //$->from(Entity\CourtClosing::class, $qb->expr('YEAR','c.date'));
        
        // baffled as to how to do this with the QueryBuilder, so...

        $dql = 'SELECT YEAR(c.date) year, COUNT(c.id) dates
            FROM InterpretersOffice\Entity\CourtClosing c
            GROUP BY year ORDER BY c.date DESC';
         return $this->getEntityManager()->createQuery($dql)->getArrayResult();
     }

}
