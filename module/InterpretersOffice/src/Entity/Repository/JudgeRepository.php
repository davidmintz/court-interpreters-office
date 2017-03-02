<?php
/**
 *  module/InterpretersOffice/src/Entity/Repository/JudgeRepository.php.
 */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * custom repository class for Judge entity.
 */
class JudgeRepository extends EntityRepository
{
    use ResultCachingQueryTrait;
    
    /**
     * @var string cache id
     */
    protected $cache_id = 'judges';

    /**
     * gets all the Judge entities, sorted.
     *
     * @return array
     */
    public function findAll()
    {
        $dql = 'SELECT j FROM InterpretersOffice\Entity\Judge j '
               .'ORDER BY j.lastname, j.firstname';

        return $this->createQuery($dql)->getResult();
    }
}
