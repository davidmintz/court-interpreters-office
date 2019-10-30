<?php /** module/Rotation/src/Entity/RotationRepository.php */

namespace InterpretersOffice\Admin\Rotation\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\QueryBuilder;
//use InterpretersOffice\Entity;
//
use InterpretersOffice\Entity\Repository\CacheDeletionInterface;
use \DateTime;

/**
 * Rotation repository
 *
 */
class RotationRepository extends EntityRepository implements CacheDeletionInterface
{
    /**
     * cache namespace
     *
     * @var string
     */
    protected $cache_namespace = 'rotations';

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
