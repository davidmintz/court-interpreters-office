<?php
/**
 *  module/InterpretersOffice/src/Entity/Repository/PersonRepository.php.
 */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use InterpretersOffice\Service\ProperNameParsingTrait;

/**
 * Person repository.
 *
 * @author david
 */
class PersonRepository extends EntityRepository
{
    use ResultCachingQueryTrait;
    use ProperNameParsingTrait;

    /**
     * the cache id
     *
     * @var string $cache_id
     */
    protected $cache_id = 'people';

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
            ['id'=>$id])->getSingleScalarResult() ? true : false;
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
            $dql .=  ' WHERE';
        }
        $dql .=  ' p.lastname LIKE :lastname';
        $parameters['lastname'] = "$name[last]%";
        if ($name['first']) {
            $dql .= ' AND p.firstname LIKE :firstname';
            $parameters['firstname'] = "$name[first]%";
        }
        if ($active !== null) {
            $dql .= ' AND p.active = '.($active ? 'TRUE':'FALSE');
        }
        $dql   .= " ORDER BY p.lastname, p.firstname";
        $query = $this->createQuery($dql)
                ->setParameters($parameters)
                ->setMaxResults($limit);

        return $query->getResult();
    }

    /**
     * ...maybe not
     */
    public function findPersonById($id)
    {
        $dql = "SELECT partial p.{lastname, firstname, middlename, email, id,
        active, home_phone}, h.name hat FROM InterpretersOffice\Entity\Person p
        WHERE p.id = :id";
        $person = $this->createQuery($dql)->setParameters($parameters)
            ->getOneorNullResult();
        return $person;

    }
}
