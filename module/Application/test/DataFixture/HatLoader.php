<?php

namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;

use Application\Entity;




class HatLoader implements FixtureInterface {
    public function load(ObjectManager $objectManager)
    {
        $hats = [
            'AUSA',
            'contract court interpreter',
            'Courtroom Deputy',
            'defense attorney',
            'Law Clerk',
            'paralegal',
            'Pretrial Services Officer',
            'staff Court Interpreter',
            'staff, Interpreters Office',
            'staff, US Attorneys Office',
            'USPO',
        ];
        foreach ($hats as $hat) {
            
            $entity = new Entity\Hat();
            $entity->setName($hat);
            $objectManager->persist($entity);
        }
        
        
        $objectManager->flush();
    }
}
