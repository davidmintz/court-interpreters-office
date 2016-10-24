<?php

namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;

use Application\Entity;

class CancellationReasonLoader implements FixtureInterface {
    
    public function load(ObjectManager $objectManager)
    {
		$reasons = [
			'interpreter not required',
			'adjourned without notice',
			'defendant not produced',
			'force majeure',
			'party did not appear',
			'other',
			'unknown',
		];
		foreach ($reasons as $r) {

			$e = (new Entity\ReasonForCancellation())->setReason($r);
			$objectManager->persist($e);	
		}
		
		$objectManager->flush();	    	

    }
}



