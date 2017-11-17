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
    
    protected $view_dql = <<<DQL
            
         SELECT e.id, 
         COALESCE(j.lastname, aj.name) AS judge, 
         t.name AS type,
         lang.name AS language,
         e.docket,
         loc.name AS location,
         ploc.name AS parent_location,
         COALESCE(p.lastname, anon_submitter.name) AS submitter
            
         FROM InterpretersOffice\Entity\Event e
         LEFT JOIN e.judge j JOIN e.eventType t
         LEFT JOIN e.anonymousJudge aj
         JOIN e.language lang            
         LEFT JOIN e.location loc
         LEFT JOIN loc.parentLocation ploc
         LEFT JOIN e.submitter p
         LEFT JOIN e.anonymousSubmitter anon_submitter
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
    
    
    public function getView($id)
    {
        
         return $this
                 ->getEntityManager()
                 ->createQuery($this->view_dql . ' WHERE e.id = :id')
                 ->setParameters(['id'=>$id])
                 ->getOneOrNullResult();

 
    }
}
