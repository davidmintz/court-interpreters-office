<?php
/**
 *  module/InterpretersOffice/src/Entity/Repository/JudgeRepository.php.
 */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;
//use Doctrine\ORM\EntityManagerInterface;

use InterpretersOffice\Entity;

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
        $dql = 'SELECT j FROM InterpretersOffice\Entity\AnonymousJudge j';
        
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
    /**
     * get data for populating Judge select menu

     * @param array $options
     * @return array
     */
    public function getJudgeOptions($options = [])
    {
        $dql = 'SELECT j.id, j.lastname, j.firstname, j.middlename, f.flavor '
                . ', l.id AS location, pl.id AS parent_location'
                . ' FROM InterpretersOffice\Entity\Judge j JOIN j.flavor f '
                . 'LEFT JOIN j.defaultLocation l '
                . 'LEFT JOIN l.parentLocation pl '
                . ' WHERE j.active = true ORDER BY j.lastname, j.firstname';
        $judges = $this->createQuery($dql, $this->cache_namespace)->getResult();
        $data = [];
        foreach ($judges as $judge) {
            $value = $judge['id'];
            $label = "$judge[lastname], $judge[firstname]";
            if ($judge['middlename']) {
                if (strlen($judge['middlename']) == 2) {
                    $label .= " $judge[middlename]";
                } else { // abbreviate it
                    $label .= " {$judge['middlename'][0]}.";
                }
            }
            $label .= ", $judge[flavor]";
            $attributes = [
                'data-default_location' => $judge['location'],
                'data-default_parent_location' => $judge['parent_location'],
            ];
            $data[] = compact('label','value','attributes');
        }
        
        if (isset($options['include_pseudo_judges']) 
                && $options['include_pseudo_judges']) {
            $data = array_merge($data,$this->getPseudoJudgeOptions());
            usort($data,function($a,$b){
                return strnatcasecmp($a['label'], $b['label']);
            });
        }
        
        return $data;     
    }
    
    /**
     * gets pseudo-judges
     * 
     * helper to get anonymous (a/k/a pseudo-) judges for populating a select
     * elements
     * 
     * @return array
     */
    protected function getPseudoJudgeOptions()
    {
        $data = [];
        $pseudojudge_dql = 'SELECT j.id, j.name, l.name as location, l.id '
                . ' AS default_location_id, p.id AS default_parent_location_id, '
                    . 'p.name as parent_location '
                    . 'FROM InterpretersOffice\Entity\AnonymousJudge j '
                    . 'LEFT JOIN j.defaultLocation l '
                    . 'LEFT JOIN l.parentLocation p '
                    . 'ORDER BY j.name, location, parent_location';
        $pseudo_judges = $this->createQuery(
            $pseudojudge_dql,
            $this->cache_namespace)->getResult();
        foreach ($pseudo_judges as $pjudge) {
            $value = $pjudge['id'];
            $label = $pjudge['name'];
            if ($pjudge['location']) {
                $label .= " - $pjudge[location]";
            }
            if ($pjudge['parent_location']) {
                $label .= ", $pjudge[parent_location]";
            }
            $attributes = [
                'data-pseudojudge' => 1, 
                'data-default_location' => $pjudge['default_location_id'],
                'data-default_parent_location' => 
                    $pjudge['default_parent_location_id'],];
            $data[] = compact('label','value','attributes');                
        }       
        return $data;
    }
    
    /**
     * experimental method. @todo change argument to SomethingInterface
     * of which both Judge and AnonymousJudge are implementations?
     * 
     * @param \InterpretersOffice\Entity\AnonymousJudge $judge
     * @return string
     */
    public function getDefaultLocationString(Entity\AnonymousJudge $judge)
    {
        $dql = 'SELECT l.name, p.name AS parent '
                . 'FROM InterpretersOffice\Entity\AnonymousJudge j '
                . 'LEFT JOIN j.defaultLocation l LEFT JOIN l.parentLocation p '
                . 'WHERE j.id = :id';
        $result = $this->createQuery($dql, $this->cache_namespace)
                ->setParameters(['id' => $judge->getId()])
                ->getOneOrNullResult();
        if (! $result or !$result['name']) {
            return '';
        }
        $name = $result['name'];
        if ($result['parent']) {
            $name .= " $result[parent]";
        }
        return $name;
    }
}