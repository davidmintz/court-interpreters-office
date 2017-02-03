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

         // create a Person
        $person = new Entity\Person();
        $person->setActive(true)
            ->setFirstname('Susie')
            ->setLastname('Somebody')
            ->setHat(
                $objectManager->getRepository('InterpretersOffice\Entity\Hat')
                    ->findOneBy(
                        [
                                //'name'=>'staff, Interpreters Office',
                                'name' => 'staff court interpreter',

                            ]
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
           ->setActive(true);
        $objectManager->persist($user);
        $objectManager->flush();
        //printf("looking good at %d in %s\n",__LINE__,basename(__FILE__));
    }
}
