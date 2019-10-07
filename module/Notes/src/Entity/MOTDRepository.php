<?php /** module/Requests/src/Entity/RequestRepository.php */

namespace InterpretersOffice\Admin\Notes\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\QueryBuilder;
//use InterpretersOffice\Entity;
//
use InterpretersOffice\Entity\Repository\CacheDeletionInterface;
use \DateTime;

/**
 * MOTD repository
 *
 */
class MOTDRepository extends EntityRepository implements CacheDeletionInterface
{
    /**
     * cache namespace
     *
     * @var string
     */
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


    /**
     * gets MOTD and MOTW for $date
     * @param  DateTime $date
     * @return Array
     */
    public function getAllForDate(DateTime $date) : Array
    {
        return [
            'MOTD' => $this->findByDate($date,'MOTD'),
            'MOTW' => $this->findByDate($date,'MOTW'),
        ];
    }

    /**
     * finds MOTD|MOTW by date
     *
     * @param  string  $type
     * @param  DateTime $date
     * @return NoteInterface|null
     */
    public function findByDate(DateTime $date, string $type = 'MOTD') : ? NoteInterface
    {
        $qb = $this->getBaseQuery($type);
        if ($type == 'MOTW') {
            $column = 'week_of';
            $day_of_week = (int)$date->format('N');
            if (1 != $day_of_week) {
                $interval = sprintf('P%sD',$day_of_week - 1);
                $date->sub(new \DateInterval($interval));
            }
        } else {
            $column = 'date';
        }
        $qb->where("m.{$column} = :{$column}")
            ->setParameters([$column => $date]);

        return $qb->getQuery()->useResultCache(true)->getOneOrNullResult();
    }

    /**
     * gets basic DQL querybuilder for MOTD|MOTW
     *
     * @param  string $type
     * @return  \Doctrine\ORM\QueryBuilder
     */
    public function getBaseQuery(string $type)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $class = strtoupper($type) == 'MOTD'? MOTD::class : MOTW::class;
        $qb->select('m, c_by, cp, cph,cr, cj, m_by, mr, mj, mp, mph')
            ->from(strtoupper($type) == 'MOTD' ? MOTD::class : MOTW::class,'m')
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
