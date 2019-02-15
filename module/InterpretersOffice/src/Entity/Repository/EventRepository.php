<?php
/**
 * module/InterpretersOffice/src/Entity/Repository/EventRepository.php
 */
namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Cache\CacheProvider;

use InterpretersOffice\Entity;

/**
 * EventRepository
 *
 */
class EventRepository extends EntityRepository implements CacheDeletionInterface
{

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
         t.name AS type,
         c.category,
         lang.name AS language,
         e.docket,
         loc.name AS location,
         ploc.name AS parent_location,
         ctrm.name AS default_courtroom,
         ctrm_parent.name AS default_courthouse,
         COALESCE(aj_parent_location.name, aj_location.name) AS aj_default_location,
         COALESCE(CONCAT(p.firstname,' ',p.lastname), anon_submitter.name)
             AS submitter,
         h.name AS submitter_hat,
         e.submission_date, e.submission_time,
         user1.username AS created_by,
         e.created,
         user2.username AS last_updated_by,
         e.modified AS last_updated,
         e.comments,
         e.admin_comments,
         COALESCE(r.reason,'n/a') AS reason_for_cancellation,
         rq.id request_id, rq.comments AS submitter_comments
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
        //LEFT JOIN InterpretersOffice\Requests\Entity\Request rq WITH e = rq.event
        $dql = 'SELECT e, j, f, t, c, anon_j, anon_submitter, submitter, sh, loc,
            ploc, cr, ie,i, d FROM '.Entity\Event::class. ' e
            LEFT JOIN e.judge j
            LEFT JOIN j.flavor f
            JOIN e.eventType t
            JOIN t.category c
            LEFT JOIN e.cancellationReason cr
            LEFT JOIN e.anonymousJudge anon_j
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
        $deft_dql = 'SELECT d.surnames, d.given_names
            FROM InterpretersOffice\Entity\Event e
            JOIN e.defendants d WHERE e.id = :id';
        $event['defendants'] = $entityManager->createQuery($deft_dql)
            ->setParameters(['id' => $id])
            ->useResultCache($this->cache_enabled)->getResult();
        $interp_dql = 'SELECT i.lastname, i.firstname
            FROM InterpretersOffice\Entity\InterpreterEvent ie
            JOIN ie.interpreter i JOIN ie.event e  WHERE e.id = :id';
        $event['interpreters'] = $entityManager->createQuery($interp_dql)
            ->setParameters(['id' => $id])
            ->useResultCache($this->cache_enabled)->getResult();

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
         WHERE e.date = :date';
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
}
