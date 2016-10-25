<?php

namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;

use Application\Entity;

class JudgeLoader implements FixtureInterface {

    public function load(ObjectManager $objectManager)
    {
    	$failla = new Entity\Judge();
    	$usdj = $objectManager->getRepository('Application\Entity\JudgeFlavor')
    		->findOneBy(['flavor'=> 'USDJ']);
    	$courtroom_618 = $objectManager->getRepository('Application\Entity\Location')
    		->findOneBy(['name'=> '618']);

    	//echo "\n"; echo get_class($courtroom_618). " is the class of shit...\n";

    	$judgeHat = $objectManager->getRepository('Application\Entity\Hat')
    		->findOneBy(['name'=> 'Judge']);
	  	$failla
    		->setHat($judgeHat)
    		->setFlavor($usdj)
                 ->setActive(true)
    		->setFirstname('Katherine')
    		->setLastname('Failla');
        $failla->setDefaultLocation($courtroom_618);
    	$objectManager->persist($failla);
        
        $daniels = new Entity\Judge();
        $daniels->setHat($judgeHat)
                ->setFlavor($usdj)
    		->setFirstname('George')
    		->setLastname('Daniels')
                ->setActive(true)
                ->setDefaultLocation(
                       $objectManager->getRepository('Application\Entity\Location')
                            ->findOneBy(['name'=> '11A'])  
                 );
        
        
        $objectManager->persist($daniels);
    	$objectManager->flush();
    }
}
