<?php

/** module/InterpretersOffice/src/Entity/DefendantRepository.php */

namespace InterpretersOffice\Entity\Repository;

use InterpretersOffice\Service\ProperNameParsingTrait;
use InterpretersOffice\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Cache\CacheProvider;

use Laminas\Paginator\Paginator as LaminasPaginator;
use Laminas\Dom\Exception\RuntimeException;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use InterpretersOffice\Admin\Form\DefendantForm;
use InterpretersOffice\Entity\Defendant;


use Laminas\Log\LoggerAwareInterface;
use Laminas\Log\LoggerAwareTrait;

use PDO;

/**
 * custom EntityRepository class for the Defendant entity.
 *
 */
class DefendantRepository extends EntityRepository implements CacheDeletionInterface
{
    use ResultCachingQueryTrait;

    use ProperNameParsingTrait;
    use LoggerAwareTrait;

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

        $dql = "SELECT d.id AS value, CONCAT(d.surnames, ', ', d.given_names) "
                . ' AS label FROM  InterpretersOffice\Entity\Defendant d '
                . ' WHERE ';

        $dql .= $this->getDqlWhereClause($name, $parameters);
        $dql   .= "ORDER BY d.surnames, d.given_names";
        $query = $this->createQuery($dql)
                ->setParameters($parameters)
                ->setMaxResults($limit);

        return $query->getResult();
    }

    /**
     * gets WHERE clause for DQL query
     *
     * also modifies $parameters which are passed by reference
     *
     * @param  Array  $name       array
     * @param  Array  $parameters query parameters
     * @return string            DQL WHERE clause
     */
    protected function getDqlWhereClause(array $name, array &$parameters)
    {
        $dql = '';
        // we don't do hyphens
        if (! strstr($name['last'], '-')) {
            $dql .= 'd.surnames LIKE :surnames ';
        } else {
             $non_hypthenated = str_replace('-', ' ', $name['last']);
             $dql .= '(d.surnames LIKE :surnames OR d.surnames LIKE :non_hyphenated) ';
             $parameters['non_hyphenated'] = "$non_hypthenated%";
        }

        if ($name['first']) {
            $parameters['given_names'] = "$name[first]%";
            $dql .= 'AND d.given_names LIKE :given_names ';
        } else {
            // we don't like empty first names, so if there are any (legacy)
            // rows that are missing a first name, don't returning them
            $dql .= "AND d.given_names <> '' " ;
        }

        return $dql;
    }

    /**
     * returns defendant names wrapped in a paginator.
     *
     * @param string $search_term
     * @param int $page
     * @return LaminasPaginator
     */
    public function paginate($search_term, $page = 1)
    {
        $dql = 'SELECT d FROM InterpretersOffice\Entity\Defendant d WHERE ';
        $name = $this->parseName($search_term);
        $parameters = ['surnames' => "$name[last]%"];
        $dql .= $this->getDqlWhereClause($name, $parameters);
        $dql   .= "ORDER BY d.surnames, d.given_names";
        $query = $this->createQuery($dql)
                ->setParameters($parameters)
                ->setMaxResults(20);
        $adapter = new DoctrineAdapter(new ORMPaginator($query));
        $paginator = new LaminasPaginator($adapter);

        return $paginator->setCurrentPageNumber($page)->setItemCountPerPage(20);
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

    /**
     * finds occurences of defendant id by judge and docket number
     *
     * @param  int $id defendant id
     * @return array
     */
    public function findDocketAndJudges($id)
    {
        $dql = 'SELECT COUNT(e.id) events, e.docket,
            COALESCE(j.lastname, aj.name) judge, j.id judge_id,
            aj.id anon_judge_id
            FROM InterpretersOffice\Entity\Event e
            JOIN e.defendants d LEFT JOIN e.judge j
            LEFT JOIN e.anonymous_judge aj
            WHERE d.id = :id GROUP BY e.docket,aj.id,j.id
            ORDER BY e.docket, judge, judge_id, anon_judge_id';

        return $this->createQuery($dql)->setParameters(['id' => $id])
            ->getResult();
    }

    /**
     * whether defendant name $id has related entities
     *
     * @param int $id entity id
     * @return boolean true if related entities exist
     */
    public function hasRelatedEntities($id)
    {
        
        $pdo = $this->getEntityManager()->getConnection();
        $sql = 'SELECT (SELECT COUNT(e.id) FROM events e 
        JOIN defendants_events de ON e.id = de.event_id 
        WHERE de.defendant_id = :id) + (SELECT COUNT(r.id) 
        FROM requests r  JOIN defendants_requests dr 
        ON r.id = dr.request_id WHERE dr.defendant_id = :id) AS total';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id'=>$id]);
        $count = $stmt->fetch(\PDO::FETCH_COLUMN);
        
        return $count ? true : false;
        
    }
}
