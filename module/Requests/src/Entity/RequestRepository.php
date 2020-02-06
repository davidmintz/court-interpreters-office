<?php /** module/Requests/src/Entity/RequestRepository.php */

namespace InterpretersOffice\Requests\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Cache\CacheProvider;
use Laminas\Paginator\Paginator as LaminasPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Doctrine\ORM\QueryBuilder;
use InterpretersOffice\Entity;
use InterpretersOffice\Requests\Entity\Request;
use InterpretersOffice\Service\ProperNameParsingTrait;
/**
 * request repository
 *
 * @todo implement caching -- or else don't
 */
class RequestRepository extends EntityRepository
{
    use ProperNameParsingTrait;

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
     * counts Request entities matching certain criteria.
     *
     * work in progress
     *
     * @param  array  $options
     * @return int
     */
    // public function count(Array $options = [])
    // {
    //     $dql = 'SELECT COUNT(r.id) FROM InterpretersOffice\Requests\Entity\Request r';
    //
    //     return $this->getEntityManager()->createQuery($dql)->getSingleScalarResult();
    // }

    /**
     * gets fully-hydrated Request entity.
     *
     * ugly, but it greatly reduces SELECT queries.
     *
     * @param int $id
     * @return Request
     */
    public function getRequest($id)
    {
        $dql = 'SELECT r, e, s, h, ih, ie, de, dr, i, t, tc,
                j, jh, jf, lang, loc, ploc
            FROM InterpretersOffice\Requests\Entity\Request r
            JOIN r.submitter s
            JOIN s.hat h
            JOIN r.event_type t
            JOIN t.category tc
            JOIN r.language lang
            LEFT JOIN r.location loc
            LEFT JOIN loc.parentLocation ploc
            LEFT JOIN r.judge j
            LEFT JOIN j.hat jh
            LEFT JOIN j.flavor jf
            LEFT JOIN r.defendants dr
            LEFT JOIN r.event e
            LEFT JOIN e.interpreterEvents ie
            LEFT JOIN e.defendants de
            LEFT JOIN ie.interpreter i
            LEFT JOIN i.hat ih
            WHERE r.id = :id';
        return $this->getEntityManager()->createQuery($dql)
            ->setParameters([':id' => $id])
            ->getOneOrNullResult();
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
            ':event_type_id' => $entity->getEventType()->getId(),
            ':docket' => $entity->getDocket(),
        ];
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.anonymous_judge', 'aj')
            ->leftJoin('r.judge', 'j')
            ->join('r.event_type', 'e')
            ->join('r.language', 'l')
             ->where('r.time = :time AND r.date = :date AND r.docket = :docket')
             ->andWhere('l.id = :language_id')
             ->andWhere('r.cancelled = false')
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
     * [getPendingRequests description]
     * @param  integer $page [description]
     * @return [type]        [description]
     */
    public function getPendingRequests($page = 1)
    {
        $qb = $this->getBaseQuery();
        $qb->where('r.pending = true')->andWhere('r.cancelled = false');

        $query = $qb->getQuery()->setHydrationMode(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        $adapter = new DoctrineAdapter(new ORMPaginator($query));
        $paginator = new LaminasPaginator($adapter);
        if (! count($paginator)) {
            return null;
        }
        $paginator->setCurrentPageNumber($page)->setItemCountPerPage(20);

        return $paginator;
    }

    /**
     * gets defendant names for current page of Request entities
     * @param  LaminasPaginator $paginator
     * @return Array
     */
    public function getDefendantNamesForCurrentPage(LaminasPaginator $paginator)
    {
        $data = $paginator->getCurrentItems()->getArrayCopy();
        if (! $data) {
            return [];
        }
        $ids = array_column(array_column($data, 0), 'id');
        $defendants = $this->getDefendants($ids);

        return $defendants;
    }
    public function getScheduledRequests($page = 1)
    {
        $qb = $this->getBaseQuery();
        $qb->where('r.pending = false')
            ->andWhere('r.cancelled = false')
            ->andWhere('r.date >= :today')
            ->andWhere('r.event IS NOT NULL')
            ->setParameters(['today' => date('Y-m-d')])
            ->orderBy('r.date', 'ASC')
            ->addOrderBy('r.time', 'ASC');

        $query = $qb->getQuery()->setHydrationMode(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        $adapter = new DoctrineAdapter(new ORMPaginator($query));
        $paginator = new LaminasPaginator($adapter);
        if (! count($paginator)) {
            return null;
        }
        $paginator->setCurrentPageNumber($page)->setItemCountPerPage(20);

        return $paginator;
    }

    /**
     * creates a QueryBuilder object for fetching Request entity data
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getBaseQuery()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select(['partial r.{id,date,time,docket, extraData}',
            't.name type','j.lastname judge','j.id judge_id',
            'loc.name location','defts d','aj.name anon_judge',
            'lang.name language',])
            ->from(Request::class, 'r')
            ->join('r.event_type', 't')
            ->leftJoin('r.judge', 'j')
            ->leftJoin('r.anonymous_judge', 'aj')
            ->join('r.language', 'lang')
            ->leftJoin('r.defendants','defts')
            ->leftJoin('r.location', 'loc')
            ->orderBy('r.date', 'DESC')
            ->addOrderBy('r.time', 'ASC');

        return $qb;
    }

    /**
     * searches for Request entities
     *
     * @param  Array   $criteria search parameters
     * @param  integer $page
     * @return LaminasPaginator|null
     */
    public function search(Array $criteria, int $page = 1) : LaminasPaginator
    {

        $qb = $this->getBaseQuery();
        // get these columns for computing permissions. so intuitive, right?
        $qb->addSelect('s.id submitter_id')->join('r.submitter','s')
            ->addSelect('c.category category')->join('t.category','c');
        $params = [];
        if (!empty($criteria['language'])) {
            $qb->where('lang.id = :language_id');
            $params['language_id'] = $criteria['language'];
        }
        if (!empty($criteria['docket'])) {
            $qb->andWhere('r.docket = :docket');
            $params['docket'] = $criteria['docket'];
        }
        if (!empty($criteria['date-from'])) {
            $qb->andWhere('r.date >= :min_date');
            $params['min_date'] = new \DateTime($criteria['date-from']);
        }
        if (!empty($criteria['date-to'])) {
            $qb->andWhere('r.date <= :max_date');
            $params['max_date'] = new \DateTime($criteria['date-to']);
        }
        if (! empty($criteria['defendant-name'])) {
            $name = $this->parseName($criteria['defendant-name']);
            $qb2 = $this->getEntityManager()->createQueryBuilder();
            $qb2->select('r2.id')->from(Request::class,'r2')->join('r2.defendants', 'd2')
                ->where($qb2->expr()->like('d2.surnames',$qb->expr()->literal("{$name['last']}%")));
            if ($name['first']) {
                $qb2->andWhere($qb->expr()->like(
                    'd2.given_names',$qb->expr()->literal("{$name['first']}%")));
            }
            $qb->andWhere($qb->expr()->in('r.id',$qb2->getDQL()));

        }
        if (! empty($criteria['judge'])) {
            $qb->andWhere('j.id = :judge_id');
            $params['judge_id'] = $criteria['judge'];
        } elseif (! empty($criteria['pseudo_judge'])) {
            $qb->andWhere('aj.id = :judge_id');
            $params['judge_id'] = $criteria['judge'];
        }
        //->leftJoin('r.event', 'e');

        $query = $qb->setParameters($params)->getQuery();
        $query->setHydrationMode(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        $adapter = new DoctrineAdapter(new ORMPaginator($query));
        $paginator = new LaminasPaginator($adapter);

        return $paginator->setCurrentPageNumber($page)->setItemCountPerPage(20);
        // $qb = $this->getEntityManager()->createQueryBuilder()
        //     ->select('r,type,lang, j, jhat, jflav,mod_by, mod_by_p,
        //         mod_by_judges, mod_by_role, submitter, submitter_hat, defts')
        //     ->from(Request::class, 'r')
        //     ->join('r.event_type', 'type')
        //     ->join('r.language', 'lang')
        //     ->join('r.modified_by', 'mod_by')
        //     ->join('mod_by.person', 'mod_by_p')
        //     ->join('mod_by.role', 'mod_by_role')
        //     ->leftJoin('mod_by.judges', 'mod_by_judges')
        //     ->join('r.submitter','submitter')
        //     ->join('submitter.hat','submitter_hat')
        //     ->leftJoin('r.defendants', 'defts')
        //     ->leftJoin('r.judge', 'j')
        //     ->leftJoin('j.hat', 'jhat')
        //     ->leftJoin('j.flavor', 'jflav')
        //     ->orderBy('r.date', 'DESC'); ... does not perform so well
    }

    /**
     * returns paginated Requests data for current user
     *
     * @param stdClass $user
     * @param int $page
     *
     * @return LaminasPaginator
     */
    public function list($user, $page = 1)
    {
        $parameters = [];
        $qb = $this->getBaseQuery();

        if ($user->role == 'submitter') {
            if ($user->judge_ids) {
                // $user is a Law Clerk or Courtoom Deputy, so constrain it
                // to events before their judge(s)
                $qb->where('j.id IN (:judge_ids)')
                ->andWhere('r.cancelled = false');
                $parameters['judge_ids'] = $user->judge_ids;
                // and in-court events
                $qb->join('t.category', 'c')->andWhere('c.category = :category');
                $parameters['category'] = 'in';
            } else {
                // for USPO or Pretrial Officer,fetch only requests created by
                // the current user
                $qb->join('r.submitter', 'p')->where('p.id = :person_id');
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
        $paginator = new LaminasPaginator($adapter);
        if (! count($paginator)) {
            return null;
        }
        $paginator->setCurrentPageNumber($page)->setItemCountPerPage(20);

        return $paginator;
    }


    /**
    * gets human-friendly view of a Request
    *
    * @todo revise completely. return a fully-hydrated entity object
    * rather than an array. deal with consequential fallout down the line.
    *
    * @param  int $id
    * @return array
    */
    public function view($id)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
        ->select(['r.id','r.time','r.date','r.docket','e.name type','r.cancelled',
            'lang.id language_id',
            'e.id event_type_id',
            'lang.name language','r.comments',
            'r.modified','modified_by.lastname modified_by_lastname',
            'modified_by.firstname modified_by_firstname',
            'modified_by_h.name modified_by_hat',
            'r.created','submitter.lastname submitter_lastname',
            'submitter.firstname submitter_firstname',
            'submitter.email submitter_email',
            'submitter_h.name submitter_hat',
            'loc.name location','parent_loc.name parent_location',
            'event.id event_id','r.pending',
            'event.deleted event_deleted',
            'event.date event_date', 'event.time event_time',
            'cr.reason cancellation',
            'j.id judge_id',
            'j.lastname judge_lastname','j.firstname judge_firstname',
            'j.middlename judge_middlename','j_flavor.flavor judge_flavor'
        ])
        ->from(Request::class, 'r')
        ->join('r.event_type', 'e')
        ->join('r.submitter', 'submitter')// a Person
        ->join('submitter.hat', 'submitter_h')
        ->leftJoin('r.modified_by', 'modified_by_user')
        ->join('r.language', 'lang')
        ->leftJoin('modified_by_user.person', 'modified_by')
        ->leftJoin('modified_by.hat', 'modified_by_h')
        ->leftJoin('r.location', 'loc')
        ->leftJoin('r.judge', 'j')
        ->leftJoin('j.flavor', 'j_flavor')
        ->leftJoin('loc.parentLocation', 'parent_loc')
        ->leftJoin('r.event', 'event')
        ->leftJoin('event.cancellation_reason', 'cr')
        ->where('r.id = :id')
        ->setParameters(['id' => $id]);

        $request = $qb->getQuery()->getOneorNullResult();
        if ($request) {
            $dql = 'SELECT d.surnames, d.given_names FROM '.Request::class.' r
                JOIN r.defendants d WHERE r.id = :id';
            $request['defendants'] = $this->getEntityManager()
                ->createQuery($dql)->setParameters(['id' => $id])->getResult();
            if ($request['event_id']) {
                $dql = 'SELECT i.lastname, i.firstname
                FROM InterpretersOffice\Entity\Interpreter i
                JOIN InterpretersOffice\Entity\InterpreterEvent ie
                WITH i = ie.interpreter JOIN ie.event e
                JOIN InterpretersOffice\Requests\Entity\Request r
                WITH e = r.event WHERE r.id = :id';
                $request['interpreters'] = $this->getEntityManager()
                    ->createQuery($dql)->setParameters(['id' => $id])
                    ->getResult();
            }
        }
        /*
        SELECT i.lastname,i.id FROM InterpretersOffice\Entity\Interpreter i
        JOIN InterpretersOffice\Entity\InterpreterEvent ie WITH i = ie.interpreter
        JOIN ie.event e JOIN InterpretersOffice\Requests\Entity\Request r
        WITH e = r.event WHERE r.id = :id
        */
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
            ->setParameters(['request_ids' => $request_ids])
            ->getResult();
        $defendants = [];
        foreach ($data as $row) {
            $request_id = $row['request_id'];
            if (key_exists($request_id, $defendants)) {
                $defendants[$request_id][] = $row;
            } else {
                $defendants[$request_id] = [ $row ];
            }
        }
        return $defendants;
    }

    /**
     * creates a new Event from a Request
     *
     * @param int $request_id
     * @throws \Throwable
     * @return array
     */
    public function createEventFromRequest($request_id)
    {
        $em = $this->getEntityManager();
        $request = $em->find(Request::class, $request_id);
        if (! $request) {
            return ['status' => 'error','message' => "request entity with id $request_id was not found, hence cannot be scheduled"];
        }
        if ($request->isCancelled()) {
            return ['status' => 'error','message' => "this request has just been cancelled, so it should not be scheduled"];
        }
        $existing = $request->getEvent();
        if ($existing) {
            // it's already been scheduled
            return [
                'status' => 'error',
                'message' => 'This request has already been scheduled',
                'event_id' => $existing->getId(),
            ];
        }
        $event = new Entity\Event();
        foreach (['Date','Time','Judge','Docket','Language','EventType','Comments','Location'] as $prop) {
            $event->{'set'.$prop}($request->{'get'.$prop}());
        }
        $event->addDefendants($request->getDefendants());
        $created = $request->getCreated();
        $event->setSubmitter($request->getSubmitter())
            ->setSubmissionTime($created)
            ->setSubmissionDate($created);
        //try {
        $em->persist($event);
        $request->setPending(false)->setEvent($event);
        $em->flush();

        return [
            'status' => 'success',
            'message' => 'new event has been added to the schedule',
            'event_date' => $event->getDate()->format('Y-m-d'),
            'event_id' => $event->getId(),
        ];
    }

    /**
    * alias for view()
    *
    * @param  int $id
    * @return array
    */
    public function getView($id)
    {
        return $this->view($id);
    }
}
