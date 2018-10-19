<?php
/** module/Requests/src/Entity/RequestRepository */

namespace InterpretersOffice\Requests\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Cache\CacheProvider;

use Zend\Paginator\Paginator as ZendPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;

use InterpretersOffice\Entity;
use InterpretersOffice\Requests\Entity\Request;

/**
 * RequestRepository
 * @todo implement caching -- or else don't
 */
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
     * look for near-exact duplicate records
     *
     * this has to be called after succesful validation to avoid possible
     * method calls on null
     *
     * @param  Request $entity
     * @return boolean true if $entity is a duplicate
     */
    public function findDuplicate(Request $entity)
    {
        $params = [
            ':date' => $entity->getDate(),
            ':time' => $entity->getTime(),
            ':language_id' => $entity->getLanguage()->getId(),
            ':event_type_id' =>  $entity->getEventType()->getId(),
            ':docket' => $entity->getDocket(),
        ];
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.anonymousJudge','aj' )
            ->leftJoin('r.judge','j' )
            ->join('r.eventType','e' )
            ->join('r.language','l' )
             ->where('r.time = :time AND r.date = :date AND r.docket = :docket')
             ->andWhere('l.id = :language_id')
             ->andWhere('e.id = :event_type_id');
             $judge = $entity->getJudge();
             if ($judge) {
                 $qb->andWhere('j.id = :judge_id');
                 $params[':judge_id'] = $judge->getId();
             } else {
                  $qb->andWhere('aj.id = :anonymous_judge_id');
                  $params[':anonymous_judge_id'] = $entity->getAnonymousJudge()->getId();
             }
            $id = $entity->getId();
            if ($id) {
                $qb->andWhere('r.id <> :id');
                $params[':id'] = $id;
            }
            $result = $qb->getQuery()->setParameters($params)->getResult();
            if (count($result)) {
                $duplicate = $result[0];
                // compare defendants
                $ours = $duplicate->getDefendants()->toArray();
                $theirs = $entity->getDefendants()->toArray();
                if ($ours == $theirs) {
                    return true;
                }
            }

            return false;
    }

    /**
     * pre-populates a new Request from an existing Request
     *
     * @param  Request $entity
     * @param  int  $from_id
     * @return Request|false if not found
     */
     public function populate(Request $entity, $from_id)

     {
         $existing = $this->find($from_id);
         if (! $existing) {
             return false;
         }
         return $entity
            ->setDocket($existing->getDocket())
            ->setJudge($existing->getJudge())
            ->setLocation($existing->getLocation())
            ->setLanguage($existing->getLanguage())
            ->addDefendants($existing->getDefendants());
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
        $qb->select(['partial r.{id,date,time,docket, extraData}',
            't.name type','j.lastname judge','loc.name location',
            'lang.name language'])
            ->from(Request::class, 'r')
            ->join('r.eventType','t')
            ->join('r.judge','j')
            ->join('r.language','lang')
            ->leftJoin('r.location','loc') //->leftJoin('l.type','lt')
            ->orderBy('r.date', 'DESC');
        if ($user->role == 'submitter') {
            if ($user->judge_ids) {
                // $user is a Law Clerk or Courtoom Deputy, so constrain it
                // to events before their judge(s)
                $qb->where('j.id IN (:judge_ids)');
                $parameters['judge_ids'] = $user->judge_ids;
                // and in-court events
                $qb->join('t.category','c')->andWhere('c.category = :category');
                $parameters['category'] = 'in';
            } else {
                // for USPO or Pretrial Officer,fetch only requests created by
                // the current user
                $qb->join('r.submitter','p')->where('p.id = :person_id');
                $parameters['person_id'] = $user->person_id;
                // this also works but is not necessary. for future reference:
                //$qb->join(Entity\User::class, 'u','WITH','r.submitter = u.person')
                //    ->where('u.id = :user_id');
                //$parameters['user_id'] = $user->id;
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
        $paginator->setCurrentPageNumber($page)->setItemCountPerPage(20);

        return $paginator;
    }


    /**
    * gets human-friendly view of a Request
    *
    * @param  int $id
    * @return array
    */
    public function view($id)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
        ->select(['r.id','r.time','r.date','r.docket','e.name type',
            'lang.name language','r.comments',
            'r.modified','modified_by.lastname modified_by_lastname',
            'modified_by.firstname modified_by_firstname',
            'modified_by_h.name modified_by_hat',
            'r.created','submitter.lastname submitter_lastname',
            'submitter.firstname submitter_firstname',
            'submitter_h.name submitter_hat',
            'loc.name location','parent_loc.name parent_location',
            'event.id event_id','r.pending',
            'event.date event_date', 'event.time event_time',
            'cr.reason cancellation',
            'j.lastname judge_lastname','j.firstname judge_firstname',
            'j.middlename judge_middlename','j_flavor.flavor judge_flavor'
        ])
        ->from(Request::class,'r')
        ->join('r.eventType', 'e')
        ->join('r.submitter', 'submitter')// a Person
        ->join('submitter.hat', 'submitter_h')
        ->join('r.modifiedBy','modified_by_user')
        ->join('r.language','lang')
        ->join('modified_by_user.person','modified_by')
        ->join ('modified_by.hat','modified_by_h')
        ->leftJoin('r.location','loc')
        ->leftJoin('r.judge','j')
        ->leftJoin('j.flavor','j_flavor')
        ->leftJoin('loc.parentLocation','parent_loc')
        ->leftJoin('r.event','event')
        ->leftJoin('event.cancellationReason','cr')
        ->where('r.id = :id')
        ->setParameters(['id'=>$id]);

        $request = $qb->getQuery()->getOneorNullResult();
        if ($request) {
            $dql = 'SELECT d.surnames, d.given_names FROM '.Request::class.' r
                JOIN r.defendants d WHERE r.id = :id';
            $request['defendants'] = $this->getEntityManager()
                ->createQuery($dql)->setParameters(['id'=>$id])->getResult();
        }
        // if ($request['event_id'])... maybe fetch interpreters
        //echo $this->getEntityManager()->createQuery()->setDql($qb->getDql())->getDql();
        return $request;
    }
    /**
     * gets defendant names for $request_ids
     * @param  Array $request_ids
     * @return Array
     */
    public function getDefendants(Array $request_ids)
    {
        $DQL = 'SELECT r.id request_id, d.given_names, d.surnames, d.id
        FROM InterpretersOffice\Requests\Entity\Request r
        JOIN r.defendants d WHERE r.id IN (:request_ids)';
        $data = $this->getEntityManager()->createQuery($DQL)
            ->setParameters(['request_ids'=>$request_ids])
            ->getResult();
        $defendants = [];
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
