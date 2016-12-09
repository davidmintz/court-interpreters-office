<?php

namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use InterpretersOffice\Entity;

class UserLoader implements FixtureInterface
{
    public function load(ObjectManager $objectManager)
    {
        $person = $objectManager->getRepository('InterpretersOffice\Entity\Person')
                ->findOneBy(['lastname' => 'Mintz']);

        $role = $objectManager->getRepository('InterpretersOffice\Entity\Role')
                ->findOneBy(['name' => 'manager']);

        $user = new Entity\User();

        $user->setPerson($person)
            ->setPassword('boink')
            ->setRole($role)
            ->setActive(true)
            ->setUsername('david');
        $objectManager->persist($user);

        $another_user = new Entity\User();
        $person = new Entity\Person();
        $person->setFirstname('John')
                ->setLastname('Somebody')
                ->setEmail('john_somebody@nysd.uscourts.gov')
                ->setActive(true)
                ->setHat(
                     $objectManager->getRepository('InterpretersOffice\Entity\Hat')
                        ->findOneBy(['name' => 'Law Clerk'])
                );
        $another_user->setRole(
               $objectManager->getRepository('InterpretersOffice\Entity\Role')
                ->findOneBy(['name' => 'submitter'])
            )
            ->setActive(true)
            ->setPerson($person)
            ->setPassword('gack!')
             ->addJudge($objectManager->getRepository('InterpretersOffice\Entity\Judge')
                ->findOneBy(['lastname' => 'Daniels']));
        $objectManager->persist($person);
        $objectManager->persist($another_user);
        $objectManager->flush();

        //$another_user->setPassword("something else");
        //$objectManager->flush();
    }
}
