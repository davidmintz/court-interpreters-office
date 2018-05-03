<?php

namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use InterpretersOffice\Entity;

class Hats extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $objectManager)
    {

        $submitter = $this->getReference('role-submitter');
        $manager = $this->getReference('role-manager');

        // create the Hat entities: name, anonymity, role
        $hats = [
            ['AUSA', 2, null],
            ['contract court interpreter', 0, null],
            ['Courtroom Deputy', 0, $submitter],
            ['defense attorney', 2, null],
            ['Law Clerk', 0, $submitter],
            ['paralegal', 2, null],
            ['Pretrial Services Officer', 0, $submitter],
            ['staff court interpreter', 0, $submitter],
            ['Interpreters Office staff', 0, $manager],
            ['staff, US Attorneys Office', 2, null],
            ['USPO', 0, $submitter],
            ['Magistrates', 1, null],
            ['Pretrial', 1, null],
            ['Judge', 0, null],
        ];
        foreach ($hats as $hat) {
            $entity = new Entity\Hat();
            $entity->setName($hat[0])->setAnonymity($hat[1]);
            if ($hat[2]) {
                $entity->setRole($hat[2]);
            }
            ${"hat-$hat[0]"} = $entity;
            $objectManager->persist($entity);
        }
        $objectManager->flush();
        $this->setReference('hat-contract-interpreter',${'hat-contract court interpreter'});
        $this->setReference('hat-staff-interpreter',${'hat-staff court interpreter'});
    }

    public function getDependencies()
    {
        return ['Roles'];
    }
}
