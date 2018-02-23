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

         SELECT e.id, e.date, e.time,
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
         COALESCE(CONCAT(p.firstname,' ',p.lastname), anon_submitter.name)
             AS submitter,
         h.name AS submitter_hat,
         e.submission_date, e.submission_time,
         user1.username AS created_by,
         e.created,
         user2.username AS last_updated_by,
         e.modified AS last_updated,
         e.comments,
         e.admin_comments
         FROM InterpretersOffice\Entity\Event e
         JOIN e.eventType t
         JOIN t.category c
         LEFT JOIN e.judge j
         LEFT JOIN j.flavor f
         LEFT JOIN e.anonymousJudge aj
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
DQL;

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
     * returns human-readable representation of event
     *
     * @param type $id
     * @return array|null if not found
     */
    public function getView($id)
    {
        $entityManager = $this->getEntityManager();
        $event = $entityManager
            ->createQuery($this->view_dql . ' WHERE e.id = :id')
            ->useResultCache($this->cache_enabled)
            ->setParameters(['id'=>$id])->getOneOrNullResult();
        if (! $event) {
            return null;
        }
        $deft_dql = 'SELECT d.surnames, d.given_names
            FROM InterpretersOffice\Entity\DefendantName d
            JOIN d.events e WHERE e.id = :id';
        $event['defendants'] = $entityManager->createQuery($deft_dql)
            ->setParameters(['id'=>$id])
            ->useResultCache($this->cache_enabled)->getResult();
        $interp_dql = 'SELECT i.lastname, i.firstname
            FROM InterpretersOffice\Entity\InterpreterEvent ie
            JOIN ie.interpreter i JOIN ie.event e  WHERE e.id = :id';
        $event['interpreters'] =  $entityManager->createQuery($interp_dql)
            ->setParameters(['id'=>$id])
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
        $index = array_search('other',array_column($result, 'label'));
        if ($index) {
            $tmp = $result[$index];
            unset($result[$index]);
            $result[] = $tmp;
        }
        $cache->save($id,$result);

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
    public function getSchedule(Array $options = [])
    {
        if (! isset($options['date'])) {
            $date = date('Y-m-d');
        } elseif ($options['date'] instanceof \DateTime) {
            $date = $options['date']->format('Y-m-d');
        }
        $dql = 'SELECT e.id, e.date, e.time,
         COALESCE(j.lastname, aj.name) AS judge,
         t.name AS type,
         lang.name AS language,
         e.docket, e.comments,
         loc.name AS location,
         ploc.name AS parent_location,
         cat.category,
         loc_type.type AS location_type
         FROM InterpretersOffice\Entity\Event e
         LEFT JOIN e.judge j
         JOIN e.eventType t
         JOIN t.category cat
         LEFT JOIN e.anonymousJudge aj
         JOIN e.language lang
         LEFT JOIN e.location loc
         LEFT JOIN loc.type as loc_type
         LEFT JOIN loc.parentLocation ploc
         WHERE e.date = :date
         ORDER BY e.time, e.id';

        $events = $this->getEntityManager()->createQuery($dql)
                ->setParameters([':date'=>$date])
                ->useResultCache($this->cache_enabled)
                ->getResult();

        if (! $events) {
            return [];
        }
        $ids = array_column($events,'id');
        $defendants = $this->getDefendantsForEvents($ids);
        $interpreters = $this->getInterpretersForEvents($ids);

        return compact('events','interpreters','defendants');
    }

    /**
     * gets defendant names for a given set of events
     *
     * @param array $ids array of event ids
     * @return array
     */
    public function getDefendantsForEvents(Array $ids)
    {
        $query = $this->getEntityManager()->createQuery(
         'SELECT e.id event_id, d.id, d.surnames, d.given_names FROM '
            . 'InterpretersOffice\Entity\DefendantName d JOIN d.events e '
            . 'WHERE e.id IN (:ids)'
        );
        $return = [];

        $data = $query->setParameters(['ids'=> $ids])
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
    public function getInterpretersForEvents(Array $ids)
    {
        $query = $this->getEntityManager()->createQuery(
        'SELECT e.id event_id, i.id, i.lastname, i.firstname FROM '
         . 'InterpretersOffice\Entity\InterpreterEvent ie JOIN ie.interpreter i '
         . 'JOIN ie.event e WHERE e.id IN (:ids)'
        );


        $return = [];

        $data = $query->setParameters(['ids'=> $ids])
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
    public function deleteCache($cache_id = null) {
        if ($cache_id) {
            return $this->cache->delete($cache_id);
        }
        $this->cache->setNamespace($this->cache_namespace);

        return $this->cache->deleteAll();
    }
}
