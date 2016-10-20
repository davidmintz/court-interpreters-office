<?php

namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;

use Application\Entity;

class LocationLoader implements FixtureInterface {

    public function load(ObjectManager $objectManager)
    {
    	$types = [
    		'courthouse',
			'courtroom',
			'holding cell',
			'interpreters office',
			'jail',
			'Pretrial Services office',
			'public area',
			'US Probation office',
    	];
    	foreach ($types as  $type) {
    		
    		$locationType = new Entity\LocationType();
    		$locationType->setType($type)->setComments('');
    		$objectManager->persist($locationType);
    	}
    	$objectManager->flush();
    	$repository = $objectManager->getRepository('Application\Entity\LocationType');
    	$parentlocations = [
    		// name, 	type, 	parent
    		[  '500 Pearl',  $repository->findOneBy(['type'=>'courthouse']), null, ],
    		[  '40 Foley',  $repository->findOneBy(['type'=>'courthouse']), null, ],
    	];
    	foreach ($parentlocations as $p) {
    		$locationEntity = new Entity\Location();
    		$locationEntity->setName($p[0])->setType($p[1])->setParentLocation($p[2])->setComments('');
    		$objectManager->persist($locationEntity);
    	}
    	$objectManager->flush();
    	$pearl = $objectManager->getRepository('Application\Entity\Location')
    		->findOneBy(['name' => '500 Pearl']);
    	$foley = $objectManager->getRepository('Application\Entity\Location')
    		->findOneBy(['name' => '40 Foley']);
    	$locations = [
    	
    		[ 'MCC',$repository->findOneBy(['type'=>'jail']),null,     ],
    		[ 'MDC',$repository->findOneBy(['type'=>'jail']),null,     ],
    		[ '7th floor',$repository->findOneBy(['type'=>'US Probation office']),$pearl     ],
    		[ '5th floor',$repository->findOneBy(['type'=>'Pretrial Services office']),$pearl     ],
    		[ '4th floor',$repository->findOneBy(['type'=>'holding cell']),$pearl     ],
    		[ '618',$repository->findOneBy(['type'=>'courtroom']),$foley     ],

    	];

    	foreach ($locations as $p) {

    		$locationEntity = new Entity\Location();
    		$locationEntity->setName($p[0])->setType($p[1])->setParentLocation($p[2])->setComments('');
    		$objectManager->persist($locationEntity);
    	}

    }

}