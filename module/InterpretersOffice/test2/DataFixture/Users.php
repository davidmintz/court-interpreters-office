<?php
namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use InterpretersOffice\Entity;

class Users extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $objectManager)
    {
        $hats = $objectManager->getRepository('InterpretersOffice\Entity\Hat');
        $judges =  $objectManager->getRepository('InterpretersOffice\Entity\Judge');

        // the admin user par excellence
        $mintz = $this->getReference('interpreter-mintz');
        $user_mintz = new Entity\User();
        $user_mintz->setPerson($mintz)
            ->setRole($this->getReference('role-administrator'))
            ->setLastLogin(new \DateTime('-2 days'))
            ->setPassword('boink')
            ->setCreated(new \DateTime("-3 days"))
            ->setUsername('david')->setActive(true);
        $objectManager->persist($user_mintz);

        // some submitters
        $data = [
            [  'lastname'=> 'ZorkenDoofer','firstname'=> 'Jane',
                'email'=> 'jane_zorkendoofer@nysd.uscourts.gov',
                'hat'=>'Law Clerk',
                'role'=>$this->getReference("role-submitter"),
                'judges'=>['Dinklesnort',]],

            [ 'lastname'=> 'Probationary','firstname'=> 'John','email'=> 'john_probationary@nysp.uscourts.gov',
                'hat'=>'USPO',  'role'=>$this->getReference("role-submitter"),
                'judges'=>[]],
            [ 'lastname'=> 'Somebody','firstname'=> 'John','email'=> 'john_somebody@nysd.uscourts.gov',
                'hat'=>'Courtroom Deputy',
                'role'=>$this->getReference("role-submitter"),'judges'=>['NoobieHeimer']],
        ];
        foreach($data as $shit) {
            $person = new Entity\Person();
            $person->setActive(true)
                ->setFirstname($shit['firstname'])
                ->setLastname($shit['lastname'])
                ->setHat($hats->findOneBy(['name' => $shit['hat'],]))
                ->setEmail($shit['email']);
            $objectManager->persist($person);
            // create a user entity
            $user = new Entity\User();
            $user->setPerson($person)->setActive(true)->setRole($shit['role'])
                ->setCreated(new \DateTime("-2 days"))
                ->setPassword('boink')
                ->setLastLogin(new \DateTime("-24 hours"));
            foreach($shit['judges'] as $judge) {
                $user->addJudge($judges->findOneBy(['lastname' => $judge]));
            }
            $objectManager->persist($user);
        }


        // some more administrative users
       $person = new Entity\Person();
       $person->setActive(true)
           ->setFirstname('Susie')
           ->setLastname('Somebody')
           ->setHat($hats->findOneBy(['name' => 'staff court interpreter',]))
           ->setEmail('susie_somebody@nysd.uscourts.gov');
       $objectManager->persist($person);
       // create a user entity
       $user = new Entity\User();
       $user->setPerson($person)->setRole(
          $this->getReference("role-manager")
       )->setUsername('susie')
          ->setPassword('boink')->setCreated(new \DateTime())
          ->setActive(true)->setLastLogin(new \DateTime("-24 hours"));
       $objectManager->persist($user);

       // create a person in the role "staff"
       $staff_person = new Entity\Person();
       $staff_person->setActive(true)->setFirstname('Staffie')->setLastname('Person')
               ->setEMail('staff_person@nysd.uscourts.gov')
               ->setHat($hats->findOneBy(['name' => 'Interpreters Office staff',]));
       $objectManager->persist($staff_person);
       $staff_user = new Entity\User();
       $staff_user->setPerson($staff_person)->setRole($this->getReference('role-staff')

       )->setUsername('staffie')
          ->setPassword('boink')->setCreated(new \DateTime())
          ->setActive(true)->setLastLogin(new \DateTime("-1 weeks"));

       $objectManager->persist($staff_user);
       $admin_person = new Entity\Person();
       $admin_person->setActive(true)
           ->setFirstname('Jane')
           ->setLastname('Admin')
           ->setHat($hats->findOneBy(['name' => 'staff court interpreter',]))
           ->setEmail('jane_admin@nysd.uscourts.gov');
       $objectManager->persist($admin_person);
       // create a user entity
       $user = new Entity\User();
       $user->setPerson($admin_person)->setRole(
           $objectManager->getRepository('InterpretersOffice\Entity\Role')
                        ->findOneBy(['name' => 'administrator'])
       )->setUsername('admin')
          ->setPassword('boink')->setCreated(new \DateTime("-2 days"))
          ->setActive(true)->setLastLogin(new \DateTime("-24 hours"));
       $objectManager->persist($user);

       $objectManager->flush();


    }

    public function getDependencies()
    {
        return ['Judges','Interpreters'];
    }
}
