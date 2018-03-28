<?php

/** module/InterpretersOffice/src/Entity/DefendantNameRepository.php */

namespace InterpretersOffice\Entity\Repository;

use InterpretersOffice\Service\ProperNameParsingTrait;
use InterpretersOffice\Entity;

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
class DefendantNameRepository extends EntityRepository implements CacheDeletionInterface
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

        $dql = "SELECT d.id AS value, CONCAT(d.surnames, ',  ',d.given_names) "
                . ' AS label FROM  InterpretersOffice\Entity\DefendantName d '
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
             $parameters['non_hyphenated'] = $non_hypthenated;
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
     * @return ZendPaginator
     */
    public function paginate($search_term, $page = 1)
    {
        $dql = 'SELECT d FROM InterpretersOffice\Entity\DefendantName d WHERE ';
        $name = $this->parseName($search_term);
        $parameters = ['surnames' => "$name[last]%"];
        $dql .= $this->getDqlWhereClause($name, $parameters);
        $dql   .= "ORDER BY d.surnames, d.given_names";
        $query = $this->createQuery($dql)
                ->setParameters($parameters)
                ->setMaxResults(20);
        $adapter = new DoctrineAdapter(new ORMPaginator($query));
        $paginator = new ZendPaginator($adapter);

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
            JOIN e.defendantNames d LEFT JOIN e.judge j
            LEFT JOIN e.anonymousJudge aj
            WHERE d.id = :id GROUP BY e.docket,aj.id,j.id';

        return $this->createQuery($dql)->setParameters(['id'=>$id])->getResult();
    }

    public function updateDefendantEvents(Entity\DefendantName $defendantName = null,
        Array $occurrences,Entity\DefendantName $existing_name, $duplicate_resolution)
        {
            $em = $this->getEntityManager();
            $db = $em->getConnection();
            return ['shit' => get_class($db)];

        /*
        $dql = 'SELECT de FROM InterpretersOffice\Entity\DefendantEvent de
            JOIN de.event e JOIN de.defendant d WHERE
                d.id <> :id AND e.id IN (:event_ids)';
        $deft_events = $this->getEntityManager()->createQuery($dql)
            ->setParameters(['event_ids'=>$event_ids,'id'=>$defendantName->getId()])
            ->getResult();
        //printf("we have %s results<br>",count($deft_events));
        foreach ($deft_events as $entity) {
            //printf("and shit is: %d<br>",$entity->getEvent()->getId());
            // @var InterpretersOffice\Entity\DefendantEvent $entity //
            $entity->setDefendantName($defendantName);
        }*/
    }
}
