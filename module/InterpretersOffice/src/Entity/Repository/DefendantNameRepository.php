<?php

/** module/InterpretersOffice/src/Entity/DefendantNameRepository.php */

namespace InterpretersOffice\Entity\Repository;

use InterpretersOffice\Service\ProperNameParsingTrait;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Cache\CacheProvider;

use Zend\Paginator\Paginator as ZendPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;

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
        
        $dql .= $this->getDqlWhereClause($name,$parameters);
        $dql   .= "ORDER BY d.surnames, d.givenNames";
        $query = $this->createQuery($dql)
                ->setParameters($parameters)
                ->setMaxResults($limit);
        
        return $query->getResult();
 
    }
    
    protected function getDqlWhereClause(Array $name, Array &$parameters)
    {
        $dql = '';
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
        return $dql;
        
    }
    
    
    /**
     * returns defendant names wrapped in a paginator.
     *
     * @param string $search_term
     * @param int $page
     * @return ZendPaginator
     */
    public function paginate($search_term, $page = 1)
    {
        $dql = 'SELECT d FROM InterpretersOffice\Entity\DefendantName d WHERE ';
        $name = $this->parseName($search_term);
        $parameters = ['surnames' => "$name[last]%"];
      
        $dql .= $this->getDqlWhereClause($name, $parameters);
        $dql   .= "ORDER BY d.surnames, d.givenNames";
        $query = $this->createQuery($dql)
                ->setParameters($parameters)
                ->setMaxResults(30);
        $adapter = new DoctrineAdapter(new ORMPaginator($query));
        $paginator = new ZendPaginator($adapter);
        
        return $paginator->setCurrentPageNumber($page)->setItemCountPerPage(30);
    }

    /**
     * implements cache deletion
     * 
     * @param int $cache_id optional cache id
     */
    public function deleteCache($cache_id = null)
    {

         $this->cache->setNamespace('defendants');
         $this->cache->deleteAll();
         
    }
   
}
