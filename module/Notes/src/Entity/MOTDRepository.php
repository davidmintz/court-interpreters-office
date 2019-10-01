<?php /** module/Requests/src/Entity/RequestRepository.php */

namespace InterpretersOffice\Admin\Notes\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\QueryBuilder;
use InterpretersOffice\Entity;
use InterpretersOffice\Admin\Notes\Entity\MOTD;
use InterpretersOffice\Entity\Repository\CacheDeletionInterface;
use \DateTime;

/**
 * MOTD repository
 *
 */
class MOTDRepository extends EntityRepository implements CacheDeletionInterface
{

    protected $cache_namespace = 'motd';

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
     * implements cache deletion
     */
    public function deleteCache($cache_id = null)
    {
        $cache = $this->cache;
        $cache->setNamespace($this->cache_namespace);
        $cache->deleteAll();
    }

    public function findMOTWByDate(DateTime $date)
    {
        $qb = $this->getbaseQuery('MOTW')
            ->where('m.week_of = :date')
            ->setParameters(['date'=>$date]);

        return $qb->getQuery()->useResultCache(true)->getOneOrNullResult();
    }

    public function findAllForDate(DateTime $date)
    {

    }

    public function findByDate(DateTime $date) :? MOTD
    {
        $qb = $this->getbaseQuery('MOTD')
            ->where('m.date = :date')
            ->setParameters(['date'=>$date]);

        return $qb->getQuery()->useResultCache(true)->getOneOrNullResult();

    }

    public function getBaseQuery(string $type)
    {

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('m, c_by, cp, cph,cr, cj, m_by, mr, mj, mp, mph')
            ->from(strtoupper($type) == 'MOTD'? MOTD::class : MOTW::class,'m')
            ->join('m.createdBy','c_by')
            ->join('c_by.person','cp')
            ->join('c_by.role','cr')
            ->join('cp.hat','cph')
            ->leftJoin('c_by.judges','cj')
            ->join('m.modifiedBy','m_by')
            ->join('m_by.role','mr')
            ->join('m_by.person','mp')
            ->join('mp.hat','mph')
            ->leftJoin('m_by.judges','mj');

        return $qb;
    }

}
