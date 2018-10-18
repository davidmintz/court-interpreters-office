<?php
/** module/Requests/Acl/OwnershipAssertion.php */

namespace InterpretersOffice\Requests\Acl;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;


class OwnershipAssertion implements AssertionInterface
{

    private $entityManager,$controller;

    /**
     * Constructor
     *
     */
    public function __construct($entityManager,$controller)
    {
        $this->entityManager = $entityManager;
        $this->controller = $controller;
    }

    public function assert(Acl $acl, RoleInterface $role = null,
        ResourceInterface $resource = null, $privilege = null)
    {
        //echo "thinking about it: role {$role->getRoleId()}, resource {$resource->getResourceId()}";
        echo  "considering ",get_class($role), " and ", get_class($this->controller->getEntity());
        echo " with action $privilege";
                
        return false;
    }


}
