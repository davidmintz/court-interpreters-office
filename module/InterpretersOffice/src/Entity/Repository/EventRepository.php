<?php
/**
 * module/InterpretersOffice/src/Entity/Repository/EventRepository.php
 */
namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Cache\CacheProvider;

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
         e.submission_datetime,
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
}
