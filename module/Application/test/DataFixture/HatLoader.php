<?php

namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;

use Application\Entity;




class HatLoader implements FixtureInterface {
    public function load(ObjectManager $objectManager)
    {
        $hats = [
            ['AUSA',false],
            ['contract court interpreter',false],
            ['Courtroom Deputy',false],
            ['defense attorney',true],
            ['Law Clerk',false],
            ['paralegal',false],
            ['Pretrial Services Officer',false],
            ['staff Court Interpreter',false],
            ['staff, Interpreters Office',false],
            ['staff, US Attorneys Office',false],
            ['USPO',false],
            ['Magistrates office',true],
            ['Pretrial services',true],
           
        ];
        foreach ($hats as $hat) {
            
            $entity = new Entity\Hat();
            $entity->setName($hat[0])->setAnonymous($hat[1]);
            $objectManager->persist($entity);
        }
      
        $objectManager->flush();
    }
}
