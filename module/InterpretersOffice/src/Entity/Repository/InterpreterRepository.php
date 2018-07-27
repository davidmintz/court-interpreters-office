<?php

/**  module/InterpretersOffice/src/Entity/Repository/InterpreterRepository.php */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

use Zend\Paginator\Paginator as ZendPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;

use InterpretersOffice\Entity;

/**
 * custom repository class for EventType entity.
 */
class InterpreterRepository extends EntityRepository implements CacheDeletionInterface
{
    use ResultCachingQueryTrait;

    /**
     * @var string cache namespace
     */
    protected $cache_namespace = 'interpreters';

    /**
     * cache
     *
     * @var CacheProvider
     */
    protected $cache;

    /**
     * constructor.
     *
     * @param EntityManagerInterface              $em
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class
     */
    public function __construct(EntityManagerInterface $em, \Doctrine\ORM\Mapping\ClassMetadata $class)
    {
        parent::__construct($em, $class);
        $this->cache = $em->getConfiguration()->getResultCacheImpl();
        $this->cache->setNamespace($this->cache_namespace);
    }

    /**
     * looks up Interpreters by name
     *
     * @param array $params
     */
    public function findByName($params)
    {
        $name = ['lastname' => $params['lastname'], 'firstname' => $params['firstname']];
        $q = $this->getQueryDataForName($name);
        return $this->createQuery($q['dql'], $q['cache_id'], 3600)->setParameters($params)->getResult();
    }
    /**
     * gets interpreters based on search criteria
     *
     * @param Array $params
     * @param int $page
     * @return ZendPaginator|Array
     */
    public function search($params, $page = 1)
    {
        $qb = $this->createQueryBuilder('i');
        $queryParams = [];

        //https://github.com/doctrine/doctrine2/issues/2596#issuecomment-162359725
        $qb->select('PARTIAL i.{lastname, firstname, id, active, securityClearanceDate}', 'h.name AS hat')
            ->join('i.hat', 'h');

        if (! empty($params['lastname'])) {
            $qb->where('i.lastname LIKE :lastname');
            $queryParams[':lastname'] = "$params[lastname]%";
            if (isset($params['firstname'])) {
                $qb->andWhere('i.firstname LIKE :firstname');
                $queryParams[':firstname'] = "$params[firstname]%";
            }
        } else {
            //orm:run-dql "SELECT i.lastname FROM InterpretersOffice\Entity\Interpreter i
            //JOIN i.interpreterLanguages il
            //JOIN il.language l WHERE l.name = 'Spanish'"
            // keep track of whether we need to set any WHERE clauses
            $hasWhereConditions = false;

            // are they filtering for active|inactive?
            switch ($params['active']) {
                case -1:
                    $active_clause = '';
                    break;
                case 0:
                    $active_clause = 'i.active = false';
                    break;
                case 1:
                    $active_clause = 'i.active = true';
                    break;
            }
            if ($active_clause) {
                $qb->where($active_clause);
                $hasWhereConditions = true;
            }
            // are they filtering for language?
            if (! empty($params['language_id'])) {
                $method = $hasWhereConditions ? 'andWhere' : 'where';
                $qb->join('i.interpreterLanguages', 'il')
                    ->join('il.language', 'l')
                    ->$method('l.id = :id');
                $queryParams[':id'] = $params['language_id'];
            }

            // are they filtering for security clearance?
            switch ($params['security_clearance_expiration']) {
                case -1: // any status whatsoever
                    $security_expiration_clause = '';
                    break;
                case 0:  // expired
                    $security_expiration_clause = 'i.securityClearanceDate < :expiration ';
                    $queryParams[':expiration'] = new \DateTime('-2 years');
                    $hasWhereConditions = true;
                    break;
                case 1: // valid
                    $security_expiration_clause = 'i.securityClearanceDate > :expiration ';
                    $queryParams[':expiration'] = new \DateTime('-2 years');
                    $hasWhereConditions = true;
                    break;
                case -2: // NULL
                    $security_expiration_clause = 'i.securityClearanceDate IS NULL';
                    $hasWhereConditions = true;
                    break;
            }
            if ($security_expiration_clause) {
                $method = $hasWhereConditions ? 'andWhere' : 'where';
                $qb->$method($security_expiration_clause);
            }
        }

        if ($queryParams) {
            $qb->setParameters($queryParams);
        }
        $qb->orderBy('i.lastname, i.firstname');
        $query = $qb->getQuery()->useResultCache(true, null, 'interpreter-search-query');
        $adapter = new DoctrineAdapter(new ORMPaginator($query));

        $paginator = new ZendPaginator($adapter);
        //echo $qb->getDQL();
        $found = $paginator->getTotalItemCount();
        if (! $found) {
            return null;
        }
        //echo "<br>".__METHOD__. " found $found ...";
        $paginator
            ->setCurrentPageNumber($page)
            ->setItemCountPerPage(40);
        return $paginator;
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
        $this->cache->deleteAll();
        $this->cache->setNamespace('people');
        return $this->cache->deleteAll();
    }

    /**
     * helper method for finding an interpreter by name
     *
     * @param array $name
     * @return array
     */
    public function getQueryDataForName(array $name)
    {
        $params = [':lastname' => "$name[lastname]%"];
        $cache_id = "autocomplete.$name[lastname]";
        $dql = 'SELECT i.id, i.lastname, i.firstname FROM InterpretersOffice\Entity\Interpreter i '
                . 'WHERE i.lastname LIKE :lastname ';
        if ($name['firstname']) {
            $dql .= ' AND i.firstname LIKE :firstname ';
            $params[':firstname'] = "$name[firstname]%";
            $cache_id .= $name['firstname'];
        }
        $dql .= 'ORDER BY i.lastname, i.firstname';
        // lazy way to get rid of possibly unsafe characters in filename?
        // $cache_id = md5($cache_id);
        return compact('dql', 'params', 'cache_id');
    }

    /**
     * Does the Interpreter entity have a data history?
     *  @todo complete work in progress
     *
     * @param Entity\Interpreter
     * @return boolean
     */
    public function hasRelatedEntities(Entity\Interpreter $interpreter)
    {
        // has the interpreter been assigned to any events?
        $dql = 'SELECT COUNT(e.id)
            FROM InterpretersOffice\Entity\InterpreterEvent ie
            JOIN ie.event e JOIN ie.interpreter i
            WHERE i.id = :id';
        $params = ['id'=>$interpreter->getId()];
        $count = $this->createQuery($dql)
            ->setParameters($params)->getSingleScalarResult();
        if ($count) {
            return true;
        }

        // has the interpreter submitted events?
        $dql = 'SELECT COUNT(e.id) FROM InterpretersOffice\Entity\Event e
            JOIN e.submitter p WHERE p.id = :id';
        $count = $this->createQuery($dql)
            ->setParameters($params)->getSingleScalarResult();
        if ($count) {
            return true;
        }

        return false;
    }



    /**
     * returns autocompletion values
     *
     * @param string $term
     * @return array
     */
    public function autocomplete($term)
    {
        $name = $this->parseName($term);
        $query = $this->getQueryDataForName($name);
        extract($query);
        $result = $this->createQuery($dql, $cache_id, 3600)->setParameters($params)->getResult();
        $data = [];
        foreach ($result as $row) {
            $data[] = ['id' => $row['id'],'value' => "$row[lastname], $row[firstname]"];
        }
        return $data;
    }

    /**
     * parses lastname and firstname from input string
     *
     * @param string $name
     * @return Array
     */
    public function parseName($name)
    {
        $pos = strrpos($name, ',');
        if (false === $pos) {
            return ['lastname' => trim($name), 'firstname' => null];
        }
        $lastname = trim(substr($name, 0, $pos));
        $firstname = trim(substr($name, $pos + 1));
        return compact('lastname', 'firstname');
    }
    /**
     * gets active interpreters of language id $id
     *
     * @param int $id
     * @return Array
     */
    public function getInterpreterOptionsForLanguage($id)
    {
        $qb = $this->createQueryBuilder('i')
            ->select("i.id AS value, CONCAT(i.lastname, ', ',i.firstname) AS label")
                // maybe more columns later, and data attributes
            ->join('i.interpreterLanguages', 'il')
            ->join('il.language', 'l')
            ->where('l.id = :id')
            ->andWhere('i.active = true')
            ->orderBy('i.lastname, i.firstname')
            ->setParameters(['id' => $id]);
        $query = $qb->getQuery()->useResultCache(
            true,
            null,
            "interp-options-language-$id"
        );

        return $query->getResult();
    }
}
