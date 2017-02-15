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
    
}