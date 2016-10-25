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

		// to do make another Person(Hat Courtroom Deputy) and User with a Judge

		$objectManager->flush();	



    }
}



