<?php
/**
 *  module/InterpretersOffice/src/Entity/Repository/PersonRepository.php.
 */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use InterpretersOffice\Service\ProperNameParsingTrait;
use InterpretersOffice\Entity\Person;
use Zend\Paginator\Paginator as ZendPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Doctrine\ORM\Query;

/**
 * Person repository.
 *
 * @author david
 */
class PersonRepository extends EntityRepository implements CacheDeletionInterface
{
    use ResultCachingQueryTrait;
    use ProperNameParsingTrait;

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
     * cache namespace
     *
     * @var string $cache_namespace
     */
    protected $cache_namespace = 'people';

    /**
     * Gets "submitter" option data for events form
     *
     * If provided an optional $person_id, we make sure to fetch that person
     * along with the results because the person might be "inactive," ergo
     * not selected by default
     *
     * @param int $hat_id hat id of people to fetch
     * @param int $person_id
     * @return array
     */
    public function getPersonOptions($hat_id, $person_id = null)
    {
        $dql = "SELECT DISTINCT p.id AS value, CONCAT(p.lastname, ', ', p.firstname) AS label "
            . 'FROM InterpretersOffice\Entity\Person p JOIN p.hat h '
            . 'WHERE (h.id = :hat_id AND p.active = true)';
        if ($person_id) {
            $dql .= " OR p.id = $person_id";
        }
        $dql .= ' ORDER BY p.lastname, p.firstname';
        return $this->createQuery($dql)
                ->setParameters(['hat_id' => $hat_id])
                ->getResult();
    }


    /**
     * does this Person $id have a data history?
     *
     * @param int $id person id
     * @return boolean true if the Person has requested an interpreter
     */
    public function hasRelatedEntities($id)
    {
        $dql = 'SELECT COUNT(e.id) FROM InterpretersOffice\Entity\Person p
            JOIN p.events e  WHERE p.id = :id';

        return $this->getEntityManager()->createQuery($dql)->setParameters(
            ['id' => $id]
        )->getSingleScalarResult() ? true : false;
    }

    /**
     * returns an array of value => label for person autocompletion
     *
     * @param string $term
     * @param int $hat hat
     * @param int $active
     * @param int $limit max number of rows
     */
    public function autocomplete($term, $hat = null, $active = null, $limit = 20)
    {
        $name = $this->parseName($term);
        $parameters = ['lastname' => "$name[last]%"];

        $dql = "SELECT p.id AS value, CONCAT(p.lastname, ', ', p.firstname) AS label"
                . '  FROM InterpretersOffice\Entity\Person p ';
        if ($hat) {
            $dql .= ' JOIN p.hat h WHERE h.id = :hat AND';
            $parameters['hat'] = $hat;
        } else {
            $dql .= ' WHERE';
        }
        $dql .= ' p.lastname LIKE :lastname';
        $parameters['lastname'] = "$name[last]%";
        if ($name['first']) {
            $dql .= ' AND p.firstname LIKE :firstname';
            $parameters['firstname'] = "$name[first]%";
        }
        if ($active !== null) {
            $dql .= ' AND p.active = '.($active ? 'TRUE' : 'FALSE');
        }
        $dql   .= " ORDER BY p.lastname, p.firstname";
        $query = $this->createQuery($dql)
                ->setParameters($parameters)
                ->setMaxResults($limit);

        return $query->getResult();
    }

    /**
     * look up people
     *
     * @param array $parameters
     * @return \Zend\Zend\Paginator\Paginator
     */
    public function search(array $parameters)
    {
        if (isset($parameters['page']) && is_numeric($parameters['page'])) {
            $page = $parameters['page'];
        } else {
            $page = 1;
        }
        // this partial syntax is NOT optional.
        // https://github.com/doctrine/doctrine2/issues/2596#issuecomment-162359725
        $dql = 'SELECT partial p.{lastname, firstname, id, active, email, mobile_phone,
            office_phone }, h.name hat FROM '. Person::class .' p JOIN p.hat h ';
        $where = [];
        $p = [];
        // if we have an id, use it and nothing else
        if (isset($parameters['id'])) {
            $where[] = 'p.id = :id';
            $p['id'] = $parameters['id'];
        } else {
        // use "hat", "active" and "name" parameters
            if (isset($parameters['active']) && $parameters['active'] !== '') {
                $where[] = 'p.active = '.($parameters['active'] ? "true" : "false");
            }
            if (isset($parameters['hat']) && $parameters['hat'] !== '') {
                $where[] = 'h.id = :hat';
                $p['hat'] = $parameters['hat'];
            }
            if (isset($parameters['name']) && $parameters['name'] !== '') {
                $fullname = $this->parseName($parameters['name']);
                foreach ($fullname as $name => $value) {
                    if ($value) {
                        $where[] = "p.{$name}name LIKE :{$name}name";
                        $p["{$name}name"] = $value . '%';
                    }
                }
            }
        }
        if ($where) {
            $dql .= ' WHERE ' . implode(' AND ', $where) ;
        }
        $dql .= ' ORDER BY p.lastname, p.firstname, h.name';
        $query = $this->createQuery($dql)->setParameters($p)
            ->setHydrationMode(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        $adapter = new DoctrineAdapter(new ORMPaginator($query));
        $paginator = new ZendPaginator($adapter);
        $items_per_page = isset($parameters['items_per_page']) ?
            $parameters['items_per_page'] : 20;
        $paginator->setCurrentPageNumber($page)
            ->setItemCountPerPage($items_per_page);

        return $paginator;
    }

    /**
     * implements cache deletion
     *
     * @param string $cache_id
     */
    public function deleteCache($cache_id = null)
    {
        $cache = $this->cache;
        $cache->setNamespace($this->cache_namespace);
        $cache->deleteAll();
    }

    public function findPerson($id)
    {
        $dql = 'SELECT p, u FROM InterpretersOffice\Entity\Person p
        LEFT JOIN InterpretersOffice\Entity\User u
        WITH p = u.person WHERE p.id = :id ';
        return $this->createQuery($dql)->setParameters([':id'=>$id])
            ->getResult();

    }
}
