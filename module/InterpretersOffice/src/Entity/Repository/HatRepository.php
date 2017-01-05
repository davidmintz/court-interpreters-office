<?php
/**
 *  module/InterpretersOffice/src/Entity/Repository/HatRepository.php.
 */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Hat repository.
 *
 * @author david
 */
class HatRepository extends EntityRepository
{
    /**
     * returns Hat entities for Person form's select element.
     *
     * this excludes the Hat entities that correspond to subtypes of the
     * Person entity
     *
     * @return array
     */
    public function getHatsForPersonForm()
    {
        $dql = 'SELECT h FROM InterpretersOffice\Entity\Hat h '
           .'WHERE h.name NOT LIKE \'%court interpreter%\' AND h.name <> \'Judge\''
           .' AND h.role IS NULL';
        $query = $this->getEntityManager()->createQuery($dql);

        return $query->getResult();
    }
}
