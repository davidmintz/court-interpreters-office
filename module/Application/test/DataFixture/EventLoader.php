<?php

namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;

use Application\Entity;

class EventLoader implements FixtureInterface {
    
    public function load(ObjectManager $objectManager)
    {
		$date = new \DateTime("next monday");

		$time = new \DateTime('10:00 am');

		$judge = $objectManager->getRepository('Application\Entity\Judge')
			->findOneBy(['lastname'=>'Failla']);

		$location = $judge->getDefaultLocation();

		$language = $objectManager->getRepository('Application\Entity\Language')
			->findOneBy(['name'=>'Spanish']);
		
		$eventType = $objectManager->getRepository('Application\Entity\EventType')
			->findOneBy(['name'=>'pretrial conference']);
		 
		$comments = 'test one two';

		$dql = "SELECT u FROM Application\Entity\User u JOIN u.person p WHERE p.lastname = 'Mintz'";

		$query = $objectManager->createQuery($dql);

		$user = $query->getSingleResult();

		$event = new Entity\Event();

		$interpreter = $objectManager->getRepository('Application\Entity\Interpreter')
			->findOneBy(['lastname'=>'Mintz']);
		  

		//$objectManager->flush();	



    }
}



