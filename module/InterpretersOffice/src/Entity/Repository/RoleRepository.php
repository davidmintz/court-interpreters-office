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
     * gets default Role entities for initializing Userfieldset role element
     *
     * @param string $auth_user_role
     * @param Entity $hat
     * @return array
     * @throws \RuntimeException
     */
    public function getRoles($auth_user_role,Entity\Hat $hat = null)
    {
        if (! in_array($auth_user_role, ['administrator','manager'])) {
            throw new \RuntimeException('invalid auth_user_role parameter '
                    . $auth_user_role);
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
            
            $dql = 'SELECT r FROM InterpretersOffice\Entity\Role r ';
            if (! $is_admin) {
                // select only roles non-admin is allowed to manage
                $dql .= 'WHERE r.name IN (\'submitter\',\'staff\')';
            }
            $dql .= 'ORDER BY r.name';
            
            return $this->createQuery($dql)->getResult();
        }
    }
    
    protected $role_authorization = [       
        'manager'=>['staff',],
        'administrator' => ['staff','administrator','manager'],        
    ];
    /**
     * gets valid roles based on $hat_id and current user's role
     * 
     * for dynamically re-populating Userfieldset's role element
     * based on state of Hat element and the current user's role
     * 
     * @param int $hat_id
     * @param string $user_role
     * @return array
     */
    public function getRoleOptionsForHatId($hat_id,$user_role)
    {
        $dql = 'SELECT r.id AS value, r.name AS label '
                . 'FROM InterpretersOffice\Entity\Role r ';

        $roles = $this->createQuery($dql)->getResult();
        
        $hat = (string)$this->getEntityManager()->find(Hat::class,$hat_id);
        $dql = 'SELECT r.id AS value, r.name AS label '
                . 'FROM InterpretersOffice\Entity\Role r ';
        switch ($hat) {
            case 'Courtroom Deputy':
            case 'Law Clerk':
            case 'USPO':
            case 'Pretrial Services Officer':
                $dql .= 'WHERE r.name = :role';
                $params = [':role'=> 'submitter'];
                break;
            case 'staff court interpreter':
            case 'Interpreters Office staff':
                $dql .= 'WHERE r.name IN (:roles)';
                $params = [':roles'=> $this->role_authorization[$user_role]];
            default:
                throw new \RuntimeException("hat $hat is not supported");
                break;
        }
        $dql .= ' ORDER BY r.name';
        $data =  $this->createQuery($dql)->setParameters($params)->getResult();
        return $data;
        
    }
}
