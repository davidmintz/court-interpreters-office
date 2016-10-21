<?php

namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;

use Application\Entity;

/**
* loads test fixtures for "hats" and similar
*
*/


class HatLoader implements FixtureInterface {

    public function load(ObjectManager $objectManager)
    {
        
        // create the Role entities
        foreach (['submitter','manager','administrator'] as $roleName) {
            $role = new Entity\Role;
            $role->setName($roleName);
        }
        $objectManager->flush();

        $submitter = $objectManager->getRepository('Application\Entity\Role')->findOneBy(['name'=>'submitter']);
        $manager = $objectManager->getRepository('Application\Entity\Role')->findOneBy(['name'=>'manager']);

        // create the Hat entities
        $hats = [
            ['AUSA',false,null],
            ['contract court interpreter',false,null],
            ['Courtroom Deputy',false,$submitter],
            ['defense attorney',true,null],
            ['Law Clerk',false,$submitter],
            ['paralegal',false,null],
            ['Pretrial Services Officer',false,$submitter],
            ['staff Court Interpreter',false,$submitter],
            ['staff, Interpreters Office',false,$manager],
            ['staff, US Attorneys Office',false,null],
            ['USPO',false,$submitter],
            ['Magistrates office',true,null],
            ['Pretrial services',true,null],
           
        ];
        foreach ($hats as $hat) {
            
            $entity = new Entity\Hat();
            $entity->setName($hat[0])->setAnonymous($hat[1]);
            if ($hat[2]) { $entity->setRole($hat[2]); }
            $objectManager->persist($entity);
        }
      
        $objectManager->flush();

        // create the AnonymousJudge entities
        $anonymous_judges = ['Magistrate','not applicable','unknown'];

        foreach ($anonymous_judges as $j) {
            $entity = new Entity\AnonymousJudge;
            $entity->setName($j);
            $objectManager->persist($entity);
        }
        $objectManager->flush();


    }
}
