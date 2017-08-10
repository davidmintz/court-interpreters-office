<?php
/**
 *  module/InterpretersOffice/src/Entity/Repository/JudgeRepository.php.
 */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * custom repository class for Judge entity.
 *
 */
class JudgeRepository extends EntityRepository implements CacheDeletionInterface
{
    use ResultCachingQueryTrait;

    /**
     * @var string cache namespace
     */
    protected $cache_namespace = 'judges';

    /**
     * cache
     *
     * @var CacheProvider
     */
    protected $cache;

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
     * gets all the Judge entities, sorted.
     *
     * @return array
     */
    public function findAll()
    {
        $dql = 'SELECT j FROM InterpretersOffice\Entity\Judge j '
               .'ORDER BY j.lastname, j.firstname';
        
        return $this->createQuery($dql, $this->cache_namespace)->getResult();
    }
    
    /**
     * gets all the judge entities who are "active"
     * 
     * @return array
     */
    public function findAllActive()
    {
        $dql = 'SELECT j FROM InterpretersOffice\Entity\Judge j '
                . ' WHERE j.active = true ORDER BY j.lastname, j.firstname';
        
        return $this->createQuery($dql, $this->cache_namespace)->getResult();
    }
    
    /**
     * gets anonymous/generic judges
     * 
     * it may be a capital crime to put this here rather than in a separate 
     * AnonymousJudgeRepository class, but for now, here it is
     * 
     * @return array
     */
        public function getAnonymousJudges()
    {
        $dql = 'SELECT j FROM InterpretersOffice\Entity\AnonymousJudge j ';
        
        return $this->createQuery($dql, $this->cache_namespace)->getResult();
    }
    
    /**
     * deletes cache
     *
     * implements CacheDeletionInterface
     * @param string $cache_id
     */
    public function deleteCache($cache_id = null)
    {

        $this->cache->setNamespace($this->cache_namespace);
        return $this->cache->deleteAll();
    }
}
