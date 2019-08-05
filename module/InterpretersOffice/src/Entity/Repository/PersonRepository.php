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
        return $this->getSubmittedEventsCount($id) ? true : false;
    }

    /**
     * returns an array of value => label for person autocompletion
     *
     * @todo consider rewriting with the querybuilder
     *
     * @param string $term
     * @param Array $options
     *
     * @return Array
     */
    public function autocomplete($term, Array $options = [])
    {
        $options = array_merge(['value_column'=>'id','hat' => null,'active' => null, 'limit' => 20, ], $options);
        $name = $this->parseName($term);
        $parameters = ['lastname' => "$name[last]%"];
        $dql = "SELECT p.{$options['value_column']} AS value,
            CONCAT(p.lastname, ', ', p.firstname) AS label, h.name AS hat";
        $dql .= '  FROM InterpretersOffice\Entity\Person p JOIN p.hat h';
        if ($options['hat']) {
            $dql .= ' WHERE h.id = :hat AND';
            $parameters['hat'] = $options['hat'];
        } else {
            $dql .= ' WHERE';
        }
        $dql .= ' p.lastname LIKE :lastname';
        $parameters['lastname'] = "$name[last]%";
        if ($name['first']) {
            $dql .= ' AND p.firstname LIKE :firstname';
            $parameters['firstname'] = "$name[first]%";
        }
        if ($options['active'] !== null) {
            $dql .= ' AND p.active = '.($options['active'] ? true : false);
        }
        $dql   .= " ORDER BY p.lastname, p.firstname";
        $query = $this->createQuery($dql)
                ->setParameters($parameters)
                ->setMaxResults($options['limit']);

        return $query->getResult();
    }

    /**
     * returns a paginator of Person entities
     *
     * @param  Array  $terms search terms
     * @return ZendPaginator
     */
    public function paginate(Array $terms)
    {
        if (!empty((int)$terms['page'])) {
            $page = $terms['page'];
        } else {
            $page = 1;
        }
        $params = [];
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('p, h, r')
            ->from(Person::class, 'p')
            ->join('p.hat', 'h')->leftJoin('h.role','r');
        if (isset($terms['id'])) {
            $qb->where('p.id = :id');
            $params = ['id' => $terms['id']];
        } else {
            if (! empty($terms['name'])) {
                $name = $this->parseName($terms['name']);
                $qb->where("p.lastname LIKE :lastname");
                $params = ['lastname' => "$name[last]%"];
                if ($name['first']) {
                    $qb->andWhere('p.firstname LIKE :firstname');
                    $params['firstname'] = "$name[first]%";
                }
            }
            if (isset($terms['active']) && $terms['active'] !== '') {
                $qb->andWhere('p.active = :active');
                $params['active'] = (bool)$terms['active'] ;
            }
            if (isset($terms['hat']) && $terms['hat'] !== '') {
                $qb->andWhere("h.id = :hat");
                $params['hat'] = $terms['hat'];
            }
        }
        // var_dump($params); echo $qb->getDQL();exit();
        $qb->setParameters($params)->orderBy('p.lastname, p.firstname');
        $query = $qb->getQuery();
        $adapter = new DoctrineAdapter(new ORMPaginator($query));
        $paginator = new ZendPaginator($adapter);
        $paginator->setCurrentPageNumber($page)->setItemCountPerPage(20);

        return $paginator;
    }

    /**
     * look up people. destined for removal.
     * @deprecated
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

    /**
     * finds Person whose id is $id
     *
     * @param  int $id
     * @return array
     */
    public function findPerson($id)
    {
        $dql = 'SELECT p, u FROM InterpretersOffice\Entity\Person p
        LEFT JOIN InterpretersOffice\Entity\User u
        WITH p = u.person WHERE p.id = :id ';

        return $this->createQuery($dql)->setParameters([':id' => $id])
            ->getResult();
    }

    /**
     * gets number of events submitted by a Person
     *
     * this is repeated elsewhere... bad dog!
     *
     * @param int $id the Person id
     * @return int
     */
    public function getSubmittedEventsCount($id)
    {
        $dql = 'SELECT COUNT(e.id) FROM InterpretersOffice\Entity\Event e
            JOIN e.submitter p WHERE p.id = :id';

        return $this->getEntityManager()->createQuery($dql)
            ->setParameters(['id' => $id])->getSingleScalarResult();
    }

    /**
     * look for person by email and return as array
     * @param  string $email
     * @return array
     */
    public function findPersonByEmail($email)
    {
        $dql = 'SELECT p.id, p.active, h.name AS hat, p.firstname, p.lastname
            FROM InterpretersOffice\Entity\Person p
            JOIN p.hat h
            WHERE p.email = :email';
        return $this->createQuery($dql)->setParameters([':email' => $email])
                ->getResult();
    }

    public function view(int $id)
    {
        $dql = 'SELECT p, h FROM InterpretersOffice\Entity\Person p
        JOIN p.hat h WHERE p.id = :id';
        return $this->getEntityManager()->createQuery($dql)
            ->setParameters(['id'=>$id])
            ->getOneOrNullResult();
    }
}
