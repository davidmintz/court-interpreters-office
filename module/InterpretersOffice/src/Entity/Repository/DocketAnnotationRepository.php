<?php
declare(strict_types=1);
namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use InterpretersOffice\Entity\Repository\CacheDeletionInterface;
use Doctrine\ORM\EntityManagerInterface;

class DocketAnnotationRepository extends EntityRepository implements
    CacheDeletionInterface
{

    private $cache_namespace = 'annotations';

    /**
     * constructor
     *
     * @param EntityManagerInterface  $em    The EntityManager to use.
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class The class descriptor.
     */
    public function __construct(EntityManagerInterface $em, ClassMetadata $class)
    {
       parent::__construct($em, $class);
       $config = $em->getConfiguration();
       $this->cache = $config->getResultCacheImpl();
       $this->cache->setNamespace($this->cache_namespace);
    }

    /**
     * gets annotations for $docket
     * @param  string $docket
     * @return array
     */
    public function findByDocket(string $docket) : array
    {
        $dql = 'SELECT a, c.username created_by, m.username modified_by
        FROM InterpretersOffice\Entity\DocketAnnotation a
        JOIN a.created_by c LEFT JOIN a.modified_by m
        WHERE a.docket = :docket ORDER BY a.created DESC';

        return $this->getEntityManager()->createQuery($dql)
            ->setParameters(['docket'=>$docket])
            ->useResultCache(true)->getResult();
    }

    /**
     * implements CacheDeletionInterface
     *
     * @param string $cache_namespace
     * @return boolean
     */
   public function deleteCache($cache_id = null) : bool
   {
       $this->cache->setNamespace($this->cache_namespace);
       return $this->cache->deleteAll();
   }

   public function countEvents(string $docket) : string
   {
       $dql = 'SELECT COUNT(e.id) FROM InterpretersOffice\Entity\Event e
       WHERE e.docket = :docket AND e.deleted = false';
       return $this->getEntityManager()->createQuery($dql)
        ->setParameters([':docket'=>$docket])
        ->useResultCache(false)->getSingleScalarResult();
   }
}
