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
            ->setRole($role)->setCreated(new \DateTime("-2 days"))
            ->setActive(true)->setLastLogin(new \DateTime("-24 hours"))
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
            ->setActive(true)->setLastLogin(new \DateTime("-24 hours"))
            ->setPerson($person)
            ->setCreated(new \DateTime())
            ->setPassword('gack!')
             ->addJudge($objectManager->getRepository('InterpretersOffice\Entity\Judge')
                ->findOneBy(['lastname' => 'Daniels']));
        $objectManager->persist($person);
        $objectManager->persist($another_user);

        $clerk_to_dinklesnort = new Entity\Person();
        $clerk_to_dinklesnort->setFirstname('Jane')
                ->setLastname('Zorkendoofer')
                ->setEmail('jane_zorkendoofer@nysd.uscourts.gov')
                ->setActive(true)
                ->setHat(
                    $objectManager->getRepository('InterpretersOffice\Entity\Hat')
                        ->findOneBy(['name' => 'Law Clerk'])
                );
        $user_account = new Entity\User();
        $user_account->setRole(
            $objectManager->getRepository('InterpretersOffice\Entity\Role')
                ->findOneBy(['name' => 'submitter'])
        )
            ->setActive(true)->setLastLogin(new \DateTime("-24 hours"))
            ->setPerson($clerk_to_dinklesnort)
            ->setPassword('gack!')->setCreated(new \DateTime())
             ->addJudge($objectManager->getRepository('InterpretersOffice\Entity\Judge')
             ->findOneBy(['lastname' => 'Dinklesnort']));
        $objectManager->persist($clerk_to_dinklesnort);
        $objectManager->persist($user_account);
        $objectManager->flush();

        //$another_user->setPassword("something else");
        //$objectManager->flush();
    }
}
