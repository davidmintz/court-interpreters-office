<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use InterpretersOffice\Entity;

/**
 * @author david
 */
class MinimalUserLoader implements FixtureInterface
{
    public function load(ObjectManager $objectManager)
    {

         // this depends on Hatloader to be run first
         (new HatLoader())->load($objectManager);

         // create a Person : staff interpreter/manager
        $person = new Entity\Person();
        $person->setActive(true)
            ->setFirstname('Susie')
            ->setLastname('Somebody')
            ->setHat(
                $objectManager->getRepository('InterpretersOffice\Entity\Hat')
                    ->findOneBy(
                        ['name' => 'staff court interpreter',]
                    )
            )
            ->setEmail('susie_somebody@nysd.uscourts.gov');
        $objectManager->persist($person);
        // create a user entity
        $user = new Entity\User();
        $user->setPerson($person)->setRole(
            $objectManager->getRepository('InterpretersOffice\Entity\Role')
                         ->findOneBy(['name' => 'manager'])
        )->setUsername('susie')
           ->setPassword('boink')
           ->setActive(true)->setLastLogin(new \DateTime("-24 hours"));
        $objectManager->persist($user);
        
        // create a person in the role "staff"
        $staff_person =  new Entity\Person();
        $staff_person->setActive(true)->setFirstname('Staffie')->setLastname('Person')
                ->setEMail('staff_person@nysd.uscourts.gov')
                ->setHat( $objectManager->getRepository('InterpretersOffice\Entity\Hat')
                    ->findOneBy(
                        ['name' => 'Interpreters Office staff',]
                    )
            );
        $objectManager->persist($staff_person);
        $staff_user = new Entity\User();
        $staff_user->setPerson($staff_person)->setRole(
            $objectManager->getRepository('InterpretersOffice\Entity\Role')
                         ->findOneBy(['name' => 'staff'])
        )->setUsername('staffie')
           ->setPassword('boink')
           ->setActive(true)->setLastLogin(new \DateTime("-1 weeks"));
        
        $objectManager->persist($staff_user);
        
        $admin_person = new Entity\Person();
        $admin_person->setActive(true)
            ->setFirstname('Jane')
            ->setLastname('Admin')
            ->setHat(
                $objectManager->getRepository('InterpretersOffice\Entity\Hat')
                    ->findOneBy(
                        ['name' => 'staff court interpreter',]
                    )
            )
            ->setEmail('jane_admin@nysd.uscourts.gov');
        $objectManager->persist($admin_person);
        // create a user entity
        $user = new Entity\User();
        $user->setPerson($admin_person)->setRole(
            $objectManager->getRepository('InterpretersOffice\Entity\Role')
                         ->findOneBy(['name' => 'administrator'])
        )->setUsername('admin')
           ->setPassword('boink')
           ->setActive(true)->setLastLogin(new \DateTime("-24 hours"));
        $objectManager->persist($user);
        $objectManager->flush();
        
    }
}
