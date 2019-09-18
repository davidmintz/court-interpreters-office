<?php
/**
 * module/InterpretersOffice/src/Entity/Repository/EventRepository.php
 */
namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Cache\CacheProvider;
use Zend\Paginator\Paginator as ZendPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Doctrine\ORM\QueryBuilder;
use InterpretersOffice\Entity;
use InterpretersOffice\Service\ProperNameParsingTrait;

/**
 * EventRepository
 *
 */
class EventRepository extends EntityRepository implements CacheDeletionInterface
{
    use ProperNameParsingTrait;

    /**
     * DQL statement for human-friendly representation of Event
     *
     * note to self: maybe re-implement with querybuilder as a learning
     * exercise, and because Doctrine recommends it?
     *
     * @var string
     */
    protected $view_dql = <<<DQL

         SELECT e.id, e.date, e.time,e.end_time,
         COALESCE(
             CONCAT(j.firstname, ' ',j.middlename,' ',j.lastname,', ',f.flavor),
         aj.name) AS judge,
         j.lastname AS judge_lastname,
         t.name AS type,
         c.category,
         lang.name AS language,
         e.docket,e.deleted,
         loc.name AS location,
         ploc.name AS parent_location,
         ctrm.name AS default_courtroom,
         ctrm_parent.name AS default_courthouse,
         COALESCE(aj_parent_location.name, aj_location.name) AS aj_default_location,
         COALESCE(CONCAT(p.firstname,' ',p.lastname), anon_submitter.name)
             AS submitter,
         h.name AS submitter_hat,
         p.email AS submitter_email,
         p.id AS submitter_id,
         e.submission_date, e.submission_time,
         user1.username AS created_by,
         e.created,
         user2.username AS last_updated_by,
         u2_role.name AS last_update_user_role,
         u2_person.lastname AS last_update_lastname,
         SUBSTRING(u2_person.firstname, 1, 1) AS last_update_firstname_init,
         e.modified AS last_updated,
         e.comments,
         e.admin_comments,
         COALESCE(r.reason,'n/a') AS reason_for_cancellation,
         rq.id request_id, rq.comments AS submitter_comments,
         rq.extraData submitter_extra_data
         FROM InterpretersOffice\Entity\Event e
         JOIN e.eventType t
         JOIN t.category c
         LEFT JOIN e.cancellationReason r
         LEFT JOIN e.judge j
         LEFT JOIN j.flavor f
         LEFT JOIN e.anonymousJudge aj
         LEFT JOIN aj.defaultLocation aj_location
         LEFT JOIN aj_location.parentLocation aj_parent_location
         JOIN e.language lang
         LEFT JOIN e.location loc
         LEFT JOIN loc.parentLocation ploc
         LEFT JOIN j.defaultLocation ctrm
         LEFT JOIN ctrm.parentLocation ctrm_parent
         LEFT JOIN e.submitter p
         LEFT JOIN p.hat h
         LEFT JOIN e.anonymousSubmitter anon_submitter
         JOIN e.createdBy user1
         LEFT JOIN e.modifiedBy user2
         LEFT JOIN user2.role u2_role
         LEFT JOIN user2.person u2_person
         LEFT JOIN InterpretersOffice\Requests\Entity\Request rq WITH e = rq.event

DQL;

    /**
     * query result cache
     *
     * @var \Doctrine\Common\Cache\Cache
     */
    protected $cache;

    /**
     * cache namespace
     *
     * @var string
     */
    protected $cache_namespace = 'events';

    /**
     * enable result cache
     *
     * for convenience during development
     *
     * @var boolean whether to enable caching
     */
    protected $cache_enabled = false;

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
     * experimental effort to avoid wasting several SELECT queries
     *
     * @param int $id of event
     * @return Entity\Event|null
     */
    public function load($id)
    {
        // LEFT JOIN InterpretersOffice\Requests\Entity\Request rq WITH e = rq.event
        // JOIN e.modifiedBy lastmod_by
        $dql = 'SELECT e, j, f, t, c, anon_j, anon_submitter, submitter, sh, loc, lang,
                ploc, cr, ie,i, d, default_loc,default_parent_loc, anon_j_default_loc
             FROM '.Entity\Event::class. ' e
            LEFT JOIN e.judge j
            LEFT JOIN j.flavor f
            JOIN e.language lang
            JOIN e.eventType t
            JOIN t.category c
            LEFT JOIN j.defaultLocation default_loc
            LEFT JOIN default_loc.parentLocation default_parent_loc
            LEFT JOIN e.cancellationReason cr
            LEFT JOIN e.anonymousJudge anon_j
            LEFT JOIN anon_j.defaultLocation anon_j_default_loc
            LEFT JOIN e.anonymousSubmitter anon_submitter
            LEFT JOIN e.location loc
            LEFT JOIN loc.parentLocation ploc
            LEFT JOIN e.submitter submitter
            LEFT JOIN submitter.hat sh
            LEFT JOIN e.interpreterEvents ie
            LEFT JOIN ie.interpreter i
            LEFT JOIN e.defendants d
            WHERE e.id = :id';

        return $this->getEntityManager()->createQuery($dql)
            ->setParameters([':id' => $id])
            ->getOneOrNullResult();
    }

    /**
     * returns human-readable representation of event
     *
     * @param int $id
     * @return array|null if not found
     */
    public function getView($id)
    {
        $entityManager = $this->getEntityManager();
        $event = $entityManager
            ->createQuery($this->view_dql . ' WHERE e.id = :id')
            ->useResultCache($this->cache_enabled)
            ->setParameters(['id' => $id])->getOneOrNullResult();
        if (! $event) {
            return null;
        }
        if ($event['submitter_extra_data']) {
            $event['submitter_extra_data'] =
            json_decode($event['submitter_extra_data'], \JSON_OBJECT_AS_ARRAY);
        }
        /* NOTE TO SELF: ugly! let's fix this at the source of the problem.*/
        $event['judge'] = str_replace('  ', ' ', $event['judge']);
        $deft_dql = 'SELECT d.surnames, d.given_names
            FROM InterpretersOffice\Entity\Event e
            JOIN e.defendants d WHERE e.id = :id';
        $event['defendants'] = $entityManager->createQuery($deft_dql)
            ->setParameters(['id' => $id])
            ->useResultCache($this->cache_enabled)->getResult();
        $interp_dql = 'SELECT i.lastname, i.firstname, i.id, i.email
            FROM InterpretersOffice\Entity\InterpreterEvent ie
            JOIN ie.interpreter i JOIN ie.event e  WHERE e.id = :id';
        $event['interpreters'] = $entityManager->createQuery($interp_dql)
            ->setParameters(['id' => $id])
            ->useResultCache($this->cache_enabled)->getResult();
        $event['is_default_location'] = false;
        if ($event['location']) {
            if ($event['parent_location']) {
                $event['location'] .= ', '.$event['parent_location'];
            }
        } elseif ($event['category'] == 'in' && $event['default_courtroom']) {
            $event['is_default_location'] = true;
            $event['location']  = $event['default_courtroom'];
            if ($event['default_courthouse']) {
                $event['location']  .= ', '.$event['default_courthouse'];
            }
        }
        $event_datetime = $event['date']->format("Y-m-d");
        if ($event['time']) {
            $event_datetime .= $event['time']->format(' H:i');
        } else {
            $event_datetime .= ' 00:00';
        }
        $event['datetime'] = $event_datetime;
        return $event;
    }

    /**
     * gets data for populating cancellation menu options
     *
     * @return Array
     */
    public function getCancellationOptions()
    {
        $id = 'cancellation_reasons';
        $cache = $this->getEntityManager()->getConfiguration()
                ->getResultCacheImpl();
        if ($cache->contains('cancellation_reasons')) {
            return $cache->fetch($id);
        }
        $dql = 'SELECT r.id AS value, r.reason AS label FROM '
                . 'InterpretersOffice\Entity\ReasonForCancellation r '
                . ' ORDER BY r.reason';
        $result = $this->getEntityManager()->createQuery($dql)->getResult();
        $index = array_search('other', array_column($result, 'label'));
        if ($index) {
            $tmp = $result[$index];
            unset($result[$index]);
            $result[] = $tmp;
        }
        $cache->save($id, $result);

        return $result;
    }

    /**
     * gets the schedule
     *
     * to be continued
     *
     * @param array $options
     * @return array
     */
    public function getSchedule(array $options = [])
    {
        if (! isset($options['date'])) {
            $date = date('Y-m-d');
        } elseif ($options['date'] instanceof \DateTime) {
            $date = $options['date']->format('Y-m-d');
        } else {
            $date = $options['date'];
        }
        $dql = 'SELECT e.id, e.date, e.time,
         COALESCE(j.lastname, aj.name) AS judge,
         COALESCE(aj_parent_location.name, aj_location.name) AS aj_default_location,
         t.name AS type,
         lang.name AS language,
         lang.id AS language_id,
         e.docket,
         e.comments,
         loc.name AS location,
         ploc.name AS parent_location,
         cat.category,
         cr.reason AS cancellation,
         loc_type.type AS location_type
         FROM InterpretersOffice\Entity\Event e
         JOIN e.language lang
         JOIN e.eventType t
         JOIN t.category cat
         LEFT JOIN e.judge j
         LEFT JOIN e.anonymousJudge aj
         LEFT JOIN aj.defaultLocation aj_location
         LEFT JOIN aj_location.parentLocation aj_parent_location
         LEFT JOIN e.location loc
         LEFT JOIN loc.type as loc_type
         LEFT JOIN loc.parentLocation ploc
         LEFT JOIN e.cancellationReason cr
         WHERE e.deleted = false AND e.date = :date';
        if (isset($options['language']) && 'all' != $options['language']) {
            $dql .= ' AND lang.name ';
            if ($options['language'] == 'spanish') {
                $dql .= " = 'Spanish'";
            } else {
                $dql .= " <> 'Spanish'";
            }
        }
         // interesting fact: if you do NOT make the sorting unique (e.g.,
         // with the id) then the sort varies randomly. IOW there is
         // no default tie-breaker.
         $dql .= ' ORDER BY e.time, e.id';

        $events = $this->getEntityManager()->createQuery($dql)
                ->setParameters([':date' => $date])
                ->useResultCache($this->cache_enabled)
                ->getResult();

        if (! $events) {
            return [];
        }
        $ids = array_column($events, 'id');
        $defendants = $this->getDefendantsForEvents($ids);
        $interpreters = $this->getInterpretersForEvents($ids);

        return compact('events', 'interpreters', 'defendants');
    }

    /**
     * gets defendant names for a given set of events
     *
     * @param array $ids array of event ids
     * @return array
     */
    public function getDefendantsForEvents(array $ids)
    {

        $DQL = 'SELECT e.id event_id, d.given_names, d.surnames, d.id
        FROM InterpretersOffice\Entity\Event e
        JOIN e.defendants d WHERE e.id IN (:ids)';
        $query = $this->getEntityManager()->createQuery($DQL);
        $return = [];

        $data = $query->setParameters(['ids' => $ids])
                ->useResultCache($this->cache_enabled)->getResult();

        foreach ($data as $deft) {
            $event_id = $deft['event_id'];
            unset($deft['event_id']);
            if (isset($return[$event_id])) {
                $return[$event_id][] = $deft;
            } else {
                $return[$event_id] = [ $deft ];
            }
        }

        return $return;
    }

    /**
     * gets interpreters for a given set of events
     *
     * @param array $ids event ids
     * @return array
     */
    public function getInterpretersForEvents(array $ids)
    {
        $query = $this->getEntityManager()->createQuery(
            'SELECT e.id event_id, i.id, i.lastname, i.firstname FROM '
            . 'InterpretersOffice\Entity\InterpreterEvent ie JOIN ie.interpreter i '
            . 'JOIN ie.event e WHERE e.id IN (:ids) ORDER BY ie.created, i.lastname'
        );


        $return = [];

        $data = $query->setParameters(['ids' => $ids])
                ->useResultCache($this->cache_enabled)->getResult();

        foreach ($data as $interpreter) {
            $event_id = $interpreter['event_id'];
            unset($interpreter['event_id']);
            if (isset($return[$event_id])) {
                $return[$event_id][] = $interpreter;
            } else {
                $return[$event_id] = [ $interpreter ];
            }
        }

        return $return;
    }

    /**
     * implements cache deletion
     *
     * @param string $cache_id
     * @return boolean true if successful(ish)
     */
    public function deleteCache($cache_id = null)
    {
        if ($cache_id) {
            return $this->cache->delete($cache_id);
        }
        $this->cache->setNamespace($this->cache_namespace);

        return $this->cache->deleteAll();
    }


    /**
     * gets modification timestamp for event $id
     *
     * used by EventsController and event-form.js to refresh form's timestamp
     * when editing defendant names causes event entity to get updated.
     *
     * @param  int $id    event id
     * @return string     modification timestamp
     */
    public function getModificationTime($id)
    {
        $dql = 'SELECT e.modified FROM InterpretersOffice\Entity\Event e
        WHERE e.id = :id';
        try {
            $result = $this->getEntityManager()->createQuery($dql)
                ->setParameters(['id' => $id])->getSingleScalarResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            $result = ['modified' => null, 'error' => 'ENTITY NOT FOUND'];
        }
        return $result;
    }

    /**
     * searches for Event entities
     *
     * @param  Array   $query search parameters
     * @param  integer $page
     * @return ZendPaginator|null
     */
    public function search(Array $query, $page = 1) : ZendPaginator
    {
        //printf("<pre>%s</pre>",print_r($query)); exit();
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e, l, t, tc, j, jf, aj, cr, loc, ploc, defts, jh, s, sh, ie, i' )
            ->from(Entity\Event::class, 'e')
            ->join('e.eventType', 't')
            ->leftJoin('e.interpreterEvents', 'ie')
            ->leftJoin('ie.interpreter', 'i')
            ->join('t.category', 'tc')
            ->leftJoin('e.judge', 'j')
            ->leftJoin('e.submitter', 's')
            ->leftJoin('s.hat', 'sh')
            ->leftJoin('j.flavor', 'jf')
            ->leftJoin('j.hat', 'jh')
            ->leftJoin('e.anonymousJudge', 'aj')
            ->join('e.language', 'l')
            ->leftJoin('e.defendants','defts')
            ->leftJoin('e.location', 'loc')
            ->leftJoin('loc.parentLocation', 'ploc')
            ->leftJoin('e.cancellationReason', 'cr')
            ->orderBy('e.date', 'DESC')
            ->addOrderBy('e.time', 'ASC');
        $params = [];
        if (!empty($query['language'])) {
            $qb->where('l.id = :language_id');
            $params['language_id'] = $query['language'];
        }
        if (!empty($query['docket'])) {
            $qb->andWhere('r.docket = :docket');
            $params['docket'] = $query['docket'];
        }
        if (!empty($query['date-from'])) {
            $qb->andWhere('e.date >= :min_date');
            $params['min_date'] = new \DateTime($query['date-from']);
        }
        if (!empty($query['date-to'])) {
            $qb->andWhere('e.date <= :max_date');
            $params['max_date'] = new \DateTime($query['date-to']);
        }
        if (! empty($query['defendant-name'])) {
            $name = $this->parseName($query['defendant-name']);
            $qb->andWhere($qb->expr()->like(
                'defts.surnames',$qb->expr()->literal("{$name['last']}%")
            ));
            if ($name['first']) {
                $qb->andWhere($qb->expr()->like(
                    'defts.given_names',$qb->expr()->literal("{$name['first']}%")
                ));
            }
        }
        if (! empty($query['judge'])) {
            $qb->andWhere('j.id = :judge_id');
            $params['judge_id'] = $query['judge'];
        } elseif (! empty($query['pseudo_judge'])) {
            $qb->andWhere('aj.id = :judge_id');
            $params['judge_id'] = $query['judge'];
        }
        $qb->setParameters($params);
        $query = $qb->getQuery();
        //$query->setHydrationMode(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        $adapter = new DoctrineAdapter(new ORMPaginator($query));
        $paginator = new ZendPaginator($adapter);

        return $paginator->setCurrentPageNumber($page)->setItemCountPerPage(20);
    }
}
