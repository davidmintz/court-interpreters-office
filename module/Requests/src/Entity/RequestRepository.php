<?php
/** module/Requests/src/Entity/RequestRepository */
namespace InterpretersOffice\Requests\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Cache\CacheProvider;

use Zend\Paginator\Paginator as ZendPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;


class RequestRepository extends EntityRepository
{

    protected $cache_namespace = 'requests';

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


    public function list($user,$page = 1)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $parameters = [];
        $qb->select(['r','t','j'])->from('InterpretersOffice\Requests\Entity\Request', 'r')
            ->join('r.eventType','t')
            ->join('r.judge','j')
            //->where($qb->expr()->like('j.lastname',':judge'))
            ->orderBy('r.date', 'ASC');

        if ($user->role == 'submitter') {
            if ($user->judge_ids) {
                // Law Clerk or Courtoom Deputy
                $qb->where('j.id IN (:judge_ids)');
                $parameters['judge_ids'] = $user->judge_ids;
            } else {
                // USPO or Pretrial Officer

            }
        }
        $query = $qb->getQuery()->setHydrationMode(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        //->setMaxResults(20);
        if ($parameters) {
            $query->setParameters($parameters);
        }
        $adapter = new DoctrineAdapter(new ORMPaginator($query));
        $paginator = new ZendPaginator($adapter);
        if (! count($paginator)) {
            return null;
        }
        $paginator->setCurrentPageNumber($page)
            ->setItemCountPerPage(20);

        return $paginator;
    }
    /**
     * returns paginated Requests data for current user
     *
     * @param stdClass $user
     * @param int $page
     *
     * @return ZendPaginator
     */
    public function __list($user,$page = 1)
    {
        //partial r.{date, time, id, docket}, t.name type
        $dql = 'SELECT
            r, t
            FROM InterpretersOffice\Requests\Entity\Request r
            JOIN r.eventType t
            ORDER BY r.date DESC';
        $query = $this->getEntityManager()->createQuery($dql)
            ->setHydrationMode(\Doctrine\ORM\Query::HYDRATE_ARRAY)
            ->setMaxResults(20);

        $adapter = new DoctrineAdapter(new ORMPaginator($query));
        $paginator = new ZendPaginator($adapter);
        if (! count($paginator)) {
            return null;
        }
        $paginator->setCurrentPageNumber($page)
            ->setItemCountPerPage(20);

        return $paginator;

    }
}
