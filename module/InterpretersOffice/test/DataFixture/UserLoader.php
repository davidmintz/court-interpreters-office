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

        $submitter_role = $objectManager->getRepository('InterpretersOffice\Entity\Role')
            ->findOneBy(['name' => 'submitter']);

        $another_user->setRole($submitter_role)
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
        $user_account->setRole($submitter_role)
            ->setActive(true)->setLastLogin(new \DateTime("-24 hours"))
            ->setPerson($clerk_to_dinklesnort)
            ->setPassword('gack!')->setCreated(new \DateTime())
             ->addJudge($objectManager->getRepository('InterpretersOffice\Entity\Judge')
             ->findOneBy(['lastname' => 'Dinklesnort']));
        $objectManager->persist($clerk_to_dinklesnort);
        $objectManager->persist($user_account);

        $other_clerk_to_dinklesnort = new Entity\Person();
        $other_clerk_to_dinklesnort->setFirstname('Bill')
                ->setLastname('Dooflicker')
                ->setEmail('bill_dooflicker@nysd.uscourts.gov')
                ->setActive(true)
                ->setHat(
                    $objectManager->getRepository('InterpretersOffice\Entity\Hat')
                        ->findOneBy(['name' => 'Courtroom Deputy'])
                );
        $objectManager->persist($other_clerk_to_dinklesnort);
        $other_user_account = (new Entity\User())->setRole($submitter_role)
            ->setActive(true)->setLastLogin(new \DateTime("-24 hours"))
            ->setPerson($other_clerk_to_dinklesnort)
            ->setPassword('gack!')->setCreated(new \DateTime())
             ->addJudge($objectManager->getRepository('InterpretersOffice\Entity\Judge')
             ->findOneBy(['lastname' => 'Dinklesnort']));
             
        $objectManager->persist($other_user_account);
        $objectManager->flush();

        //$another_user->setPassword("something else");
        //$objectManager->flush();
    }
}
