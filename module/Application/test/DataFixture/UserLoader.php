<?php

namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;

use Application\Entity;

class UserLoader implements FixtureInterface {
    
    public function load(ObjectManager $objectManager)
    {
		

		
        $person = $objectManager->getRepository('Application\Entity\Person')
                ->findOneBy(['lastname'=>'Mintz']);

        $role = $objectManager->getRepository('Application\Entity\Role')
                ->findOneBy(['name'=>'manager']);

        $user = new Entity\User;

        $user->setPerson($person)->setPassword('boink')->setRole($role)->setActive(true);
        $objectManager->persist($user);

        $another_user = new Entity\User();
        $person = new Entity\Person();
        $person->setFirstname('John')
                ->setLastname('Somebody')
                ->setEmail('john_somebody@nysd.uscourts.gov')
                ->setActive(true)
                ->setHat(
                     $objectManager->getRepository('Application\Entity\Hat')
                        ->findOneBy(['name'=>'Law Clerk']) 
                );
        $another_user->setRole(
               $objectManager->getRepository('Application\Entity\Role')
                ->findOneBy(['name'=>'submitter']) 
            )
            ->setActive(true)
            ->setPerson($person)
            ->setPassword('gack!')
             ->addJudge($objectManager->getRepository('Application\Entity\Judge')
                ->findOneBy(['lastname'=>'Daniels']) );
        $objectManager->persist($person);
        $objectManager->persist($another_user);
        $objectManager->flush();	

        //$another_user->setPassword("something else");
        //$objectManager->flush();


    }
}



