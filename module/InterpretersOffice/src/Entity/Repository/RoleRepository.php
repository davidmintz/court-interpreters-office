<?php
/**
 *  module/InterpretersOffice/src/Entity/Repository/RoleRepository.php.
 */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use InterpretersOffice\Entity;

/**
 * Role repository.
 *
 * to be continued. the plan is to use the currently authenticated user's role
 * to determine what Role entities to return for populating the User form select
 * element.
 *
 * @author david
 */
class RoleRepository extends EntityRepository
{
    use ResultCachingQueryTrait;

     /**
     * @var string cache id
     */
    protected $cache_id = 'roles';

    /**
     * gets Role entities for populating Userfieldset role element
     *
     * @param string $auth_user_role
     * @param Entity $hat
     * @return array
     * @throws \RuntimeException
     */
    public function getRoles($auth_user_role,Entity\Hat $hat = null)
    {
        if (! in_array($auth_user_role, ['administrator','manager'])) {
            throw new \RuntimeException("invalid auth_user_role parameter $auth_user_role");
        }
        $is_admin = 'administrator' === $auth_user_role;
        if (!$is_admin && $hat && $hat->getRole()) {
            // select only the roles that are valid for this hat
            $dql = 'SELECT h FROM InterpretersOffice\Entity\Hat h JOIN h.role r 
                WHERE r.id = '.$hat->getRole()->getId(). ' ORDER BY r.name ';
            $data = $this->createQuery($dql)->getResult();
            $return = [];
            $seen = '';
            foreach ($data as $object) {
                $this_role = (string)$object->getRole();
                if ($this_role == $seen) {
                    continue;
                }
                $return[] = $object->getRole();
                $seen = $this_role;
            }
            return $return;
            
        } else {
            //echo "hello?";
            $dql = 'SELECT r FROM InterpretersOffice\Entity\Role r ';
            if (! $is_admin) {
                // select only roles non-admin is allowed to manage
                $dql .= 'WHERE r.name IN (\'submitter\',\'staff\')';
            }
            $dql .= 'ORDER BY r.name';
            return $this->createQuery($dql)->getResult();
        }
    }
}
