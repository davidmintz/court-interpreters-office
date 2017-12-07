<?php
/**
 * module/InterpretersOffice/Entity/Repository/UserRepository.php
 */

namespace InterpretersOffice\Entity\Repository;

use InterpretersOffice\Entity\User;
use InterpretersOffice\Entity\Hat;
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

    public function getRoleOptionsForHatId($hat_id,$user_role)
    {
        $hat = (string)$this->getEntityManager()->find(Hat::class,$hat_id);
        switch ($hat) {
            case 'Courtroom Deputy':
            case 'Law Clerk':
            case 'USPO':
            case 'Pretrial Services Officer'
                return "submitter"; // tmp
                break;
            case 'staff court interpreter':
            case 'Interpreters Office staff':
                return 'yadda'; // tmp
                break;
            default:
                # code...
                break;
        }

        
    }
/*
                      |
+---------------------------+
| Courtroom Deputy          |
| Interpreters Office staff |
| Law Clerk                 |
| Pretrial Services Officer |
| staff court interpreter   |
| USPO                      |
+---------------------------+
       |
+---------------+
| submitter     |
| manager       |
| administrator |
| staff         |
+---------------+

*/

}
