<?php /** module/Rotation/src/Entity/SubstitutionRepository.php */

namespace InterpretersOffice\Admin\Rotation\Entity;

use Doctrine\ORM\EntityRepository;
use InterpretersOffice\Entity\Repository\CacheDeletionInterface;

/**
 * Substitution repository
 *
 */
class SubstitutionRepository extends EntityRepository implements CacheDeletionInterface
{
    /**
     * cache_namespace
     *
     * @var string
     */
    private $cache_namespace = 'rotations';

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
     * implements cache deletion
     */
    public function deleteCache($cache_id = null)
    {
        $cache = $this->cache;
        $cache->setNamespace($this->cache_namespace);
        $cache->deleteAll();
    }

}
