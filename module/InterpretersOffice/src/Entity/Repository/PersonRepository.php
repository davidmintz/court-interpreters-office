<?php
/**
 *  module/InterpretersOffice/src/Entity/Repository/PersonRepository.php.
 */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Hat repository.
 *
 * @author david
 */
class PersonRepository extends EntityRepository
{
    use ResultCachingQueryTrait;

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
}
