<?php
/** module/Requests/src/Entity/RequestRepository */

namespace InterpretersOffice\Requests\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Cache\CacheProvider;

use Zend\Paginator\Paginator as ZendPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;

use InterpretersOffice\Entity;

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

    /**
     * returns paginated Requests data for current user
     *
     * @param stdClass $user
     * @param int $page
     *
     * @return ZendPaginator
     */
    public function list($user,$page = 1)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $parameters = [];
        $qb->select(['r','t','j'])->from('InterpretersOffice\Requests\Entity\Request', 'r')
            ->join('r.eventType','t')
            ->join('r.judge','j')
            //->where($qb->expr()->like('j.lastname',':judge'))
            ->orderBy('r.date', 'DESC');

        if ($user->role == 'submitter') {
            if ($user->judge_ids) {
                // Law Clerk or Courtoom Deputy
                $qb->where('j.id IN (:judge_ids)');
                // constrain it to events before their judge(s)
                $parameters['judge_ids'] = $user->judge_ids;
                // and constrain it to in-court events
                $qb->join('t.category','c')->andWhere('c.category = :category');
                $parameters['category'] = 'in';
            } else {
                // USPO or Pretrial Officer
                //$qb->join
            }
        }
        $query = $qb->getQuery()->setHydrationMode(\Doctrine\ORM\Query::HYDRATE_ARRAY);
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

    public function getDefendants(Array $request_ids)
    {
        $DQL = 'SELECT r.id request_id, d.given_names, d.surnames, d.id
        FROM InterpretersOffice\Requests\Entity\Request r
        JOIN r.defendants d WHERE r.id IN (:request_ids)';// INDEX BY r.id'
        $data = $this->getEntityManager()->createQuery($DQL)
            ->setParameters(['request_ids'=>$request_ids])
            ->getResult();
        $defendants = [];
        //printf('<pre>%s</pre>',print_r($data,true));
        foreach ($data as $row) {
            $request_id = $row['request_id'];
            if (key_exists($request_id,$defendants)) {
                $defendants[$request_id][] = $row;
            } else {
                $defendants[$request_id] = [ $row ];
            }
        }
        return $defendants;
    }

}
