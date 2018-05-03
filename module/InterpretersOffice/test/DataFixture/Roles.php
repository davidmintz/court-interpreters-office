<?php
namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;

use Doctrine\Common\DataFixtures\AbstractFixture;
use InterpretersOffice\Entity;

class Roles extends AbstractFixture
{
    public function load(ObjectManager $objectManager)
    {
        // create the Role entities
        foreach (['submitter', 'manager', 'administrator','staff'] as $roleName) {
            $role = new Entity\Role();
            $role->setName($roleName);
            $objectManager->persist($role);
            ${"role-$roleName"} = $role;
        }
        $objectManager->flush();
        // store reference to admin role for User relation to Role
        //$this->addReference('admin-role', $adminRole);
        $this->setReference('role-administrator',${"role-administrator"});
        $this->setReference('role-manager',${"role-manager"});
        $this->setReference('role-submitter',${"role-submitter"});
        $this->setReference('role-staff',${"role-staff"});
    }
}
