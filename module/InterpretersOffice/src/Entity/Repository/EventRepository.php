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
class EventRepository extends EntityRepository
{
    
    /**
     * DQL statement for human-friendly representation of Event
     * 
     * @var string 
     */
    protected $view_dql = <<<DQL
            
         SELECT e.id, e.date, e.time, 
         COALESCE(j.lastname, aj.name) AS judge, 
         t.name AS type,
         lang.name AS language,
         e.docket,
         loc.name AS location,
         ploc.name AS parent_location,
         COALESCE(CONCAT(p.lastname,', ',p.firstname), anon_submitter.name) 
             AS submitter,
         h.name AS submitter_hat,
         e.submission_date, e.submission_time,
         user1.username AS created_by,
         e.created,
         user2.username AS last_updated_by,
         e.modified,
         e.comments,
         e.admin_comments
         FROM InterpretersOffice\Entity\Event e
         LEFT JOIN e.judge j JOIN e.eventType t
         LEFT JOIN e.anonymousJudge aj
         JOIN e.language lang            
         LEFT JOIN e.location loc
         LEFT JOIN loc.parentLocation ploc
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
        $this->cache->setNamespace('events');
    }
    
    /**
     * returns human-readable representation of event
     * 
     * @param type $id
     * @return array
     */
    public function getView($id)
    {
        
         return $this
                 ->getEntityManager()
                 ->createQuery($this->view_dql . ' WHERE e.id = :id')
                 ->setParameters(['id'=>$id])
                 ->getOneOrNullResult();
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
     * @param array $options
     * @return array
     */
    public function getSchedule(Array $options = [])
    {
        if (! isset($options['date'])) {
            $options['date'] = date('Y-m-d');
        }
        $dql = 'SELECT e.id, e.date, e.time, 
         COALESCE(j.lastname, aj.name) AS judge, 
         t.name AS type,
         lang.name AS language,
         e.docket,
         e.comments,
         loc.name AS location,
         ploc.name AS parent_location,
         cat.category
         FROM InterpretersOffice\Entity\Event e
         LEFT JOIN e.judge j 
         JOIN e.eventType t
         JOIN t.category cat
         LEFT JOIN e.anonymousJudge aj
         JOIN e.language lang            
         LEFT JOIN e.location loc
         LEFT JOIN loc.parentLocation ploc 
         WHERE e.date = :date
         ORDER BY e.time';
        
        $data = $this->getEntityManager()->createQuery($dql)
                ->setParameters([':date'=>$options['date']])
                ->getResult();
        
        return $data;
    }
    
    /**
     * gets defendant names for a given set of events
     * 
     * @param array $options
     * @return array
     */
    public function getDefendants(Array $options)
    {
        // this is bullshit as of right now
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('d.surnames')->from(Entity\DefendantName::class, 'd')
        ->join(Entity\Event::class,'e')->where('e.date = :date')->setParameters([':date'=>new \DateTime()]);
        echo $qb->getDQL();
        $result = $qb->getQuery()->getResult();
        echo " SHIT: " ,count($result);
        return $result;
    }
    
    /**
     * @param array $options
     * @return array
     */
    public function getInterpreters(Array $options)
    {
        return [];
    }
}
