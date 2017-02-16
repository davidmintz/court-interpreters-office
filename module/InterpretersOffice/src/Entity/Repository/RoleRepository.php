<?php
/**
 *  module/InterpretersOffice/src/Entity/Repository/RoleRepository.php.
 */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;

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
     * gets Role entities for populating Userfieldset role element
     * 
     * @param string $auth_user_role
     * @return array
     * @throws \RuntimeException
     */
    public function getRoles($auth_user_role)
    {        
        if (! in_array($auth_user_role,['administrator','manager'])) {
            throw new \RuntimeException("invalid auth_user_role parameter $auth_user_role");
        }
        $dql = 'SELECT r FROM InterpretersOffice\Entity\Role r ';
        if ('administrator' !== $auth_user_role) {
            $dql .= 'WHERE r.name <> \'administrator\' ';
        }
        $dql .= 'ORDER BY r.name';                
        return $this->createQuery($dql)->getResult();
    }
    
}