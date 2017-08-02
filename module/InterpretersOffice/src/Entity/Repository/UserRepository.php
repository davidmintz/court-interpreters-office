<?php
/**
 * module/InterpretersOffice/Entity/Repository/UserRepository.php
 */

namespace InterpretersOffice\Entity\Repository;

use InterpretersOffice\Entity\User;

use Doctrine\ORM\EntityRepository;

/**
 * UserRepository
 *
 *
 */
class UserRepository extends EntityRepository
{


    use ResultCachingQueryTrait;

     /**
     * @var string cache id
     */
    protected $cache_id = 'users';

    /**
     * cache lifetime
     *
     * @var int
     */
    protected $cache_lifetime = 3600;

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
        $this->cache->setNamespace('users');
    }

    /**
     * experimental overrride of find() to cut down on db queries
     *
     * ...which maybe we no longer really need because we are no longer using
     * Doctrine's authentication adapter
     *
     * @param mixed    $id          The identifier.
     * @param int|null $lockMode    One of the \Doctrine\DBAL\LockMode::* constants
     *                              or NULL if no specific lock mode should be used
     *                              during the search.
     * @param int|null $lockVersion The lock version.
     * @return User  The User instance or NULL if the entity cannot be found.
     */
    public function find($id, $lockMode = null, $lockVersion = null)
    {

        if (is_object($id)) {
            $id = $id->getId();
        }
        // this seems to resolve "array to string conversion" notices
        if (is_array($id) && isset($id['id'])) {
            $id = $id['id'];
        }
        if ($this->cache->contains($id)) {
            return $this->cache->fetch($id);
        }

        $user = parent::find($id, $lockMode, $lockVersion);
        if (! $user) {
            return null;
        }
        $this->cache->save($id, $user, $this->cache_lifetime);
        return $user;
    }
}
