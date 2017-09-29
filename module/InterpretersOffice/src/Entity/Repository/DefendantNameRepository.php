<?php

/** module/InterpretersOffice/src/Entity/DefendantNameRepository.php */

namespace InterpretersOffice\Entity\Repository;

use InterpretersOffice\Service\ProperNameParsingTrait;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Cache\CacheProvider;

/**
 * custom EntityRepository class for the DefendantName entity.
 *
 *
 */
class DefendantNameRepository extends EntityRepository 
    implements CacheDeletionInterface

{
    use ResultCachingQueryTrait;
    
    use ProperNameParsingTrait;
    
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
        $this->cache->setNamespace('defendants');
    }
    
    
    public function autocomplete($term)
    {
        
    }

    /**
     * experimental
     *
     * implements cache deletion
     */
    public function deleteCache($cache_id = null)
    {

         $this->cache->setNamespace('defendants');
         $this->cache->deleteAll();
         //return sprintf('ran %s at line %d', __METHOD__, __LINE__);
    }
   
}
