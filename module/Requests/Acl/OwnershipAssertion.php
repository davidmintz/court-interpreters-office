<?php
/** module/Requests/Acl/OwnershipAssertion.php */

namespace InterpretersOffice\Requests\Acl;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;


class OwnershipAssertion implements AssertionInterface
{

    public function assert(Acl $acl, RoleInterface $role = null,
        ResourceInterface $resource = null, $privilege = null)
    {
        return false;
    }


}
