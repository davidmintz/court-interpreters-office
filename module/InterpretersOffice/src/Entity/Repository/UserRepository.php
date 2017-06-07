<?php
/**
 * module/InterpretersOffice/Entity/Repository/UserRepository.php
 */

namespace InterpretersOffice\Entity\Repository;

use InterpretersOffice\Entity\User;

use Doctrine\ORM\EntityRepository;

/**
 * Description of UserRepository
 *
 * @author david
 */
class UserRepository extends EntityRepository {
    
    
    use ResultCachingQueryTrait;
    
    
    
     /**
     * @var string cache id
     */
    protected $cache_id = 'users';
    
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
     * @param mixed    $id          The identifier.
     * @param int|null $lockMode    One of the \Doctrine\DBAL\LockMode::* constants
     *                              or NULL if no specific lock mode should be used
     *                              during the search.
     * @param int|null $lockVersion The lock version.
     * @return User  The User instance or NULL if the entity cannot be found.
     */
    public function find($id, $lockMode = null, $lockVersion = null) {
       
        // this gets us down to zero when there's a cache hit
        if (is_object($id)) {
            $id = $id->getId();
        }
        if ($this->cache->contains($id)) {
            return $this->cache->fetch($id);
        } 

        $user = parent::find($id, $lockMode, $lockVersion);
        if (! $user) { return null; }
        $this->cache->save($id, $user, $this->cache_lifetime);        
        return $user;
    }
     /*
        // this find() reduces 3 queries to one by return the object from the
        // session (i.e., the same object we were given)
        if (is_object($id) && $id instanceof User) {
            
            // a simple 'return $id;' looks like it works 
            $DQL = 'SELECT u.id FROM InterpretersOffice\Entity\User u '
                    //. ' JOIN u.person p JOIN u.role r JOIN p.hat h '
                    . ' WHERE u.id = :id AND u.active = true';
            $q = $this->getEntityManager()->createQuery($DQL)->setParameters(['id'=>$id->getId()]);            
            if($q->getOneOrNullResult()) {
                return $id;
            }
        }         
        */
}
