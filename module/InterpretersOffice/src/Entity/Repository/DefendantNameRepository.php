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
    
    /**
     * returns an array of names for defendant autocompletion
     * 
     * @param string $term
     * @param int $limit
     * @return array
     */    
    public function autocomplete($term, $limit = 20)
    {
        $name = $this->parseName($term);
        $parameters = ['surnames' => "$name[last]%"];
        
        $dql = "SELECT d.id AS value, CONCAT(d.surnames, ',  ',d.givenNames) "
                . ' AS label FROM  InterpretersOffice\Entity\DefendantName d '
                . ' WHERE ';
        
        // we don't do hyphens
        if (! strstr($name['last'],'-')) {
            $dql .= 'd.surnames LIKE :surnames ';
        } else {
             $non_hypthenated = str_replace('-',' ',$name['last']);
             $dql .= '(d.surnames LIKE :surnames OR d.surnames LIKE :non_hyphenated) ';
             $parameters['non_hyphenated']=$non_hypthenated;
        }        
        
        if ($name['first']) {
            $parameters['givenNames'] = "$name[first]%";
            $dql .= 'AND d.givenNames LIKE :givenNames ';
        } else {
            // we don't like empty first names, so if there are any (legacy)
            // rows that are missing a first name, avoid returning them
            $dql .= "AND d.givenNames <> '' " ;
        }        
        $dql   .= "ORDER BY d.surnames, d.givenNames";       
        $query = $this->createQuery($dql)
                ->setParameters($parameters)
                ->setMaxResults($limit);
        
        return $query->getResult();
 
    }

    /**
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
