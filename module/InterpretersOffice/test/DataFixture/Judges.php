<?php
namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use InterpretersOffice\Entity;

class Judges extends AbstractFixture
{
    public function load(ObjectManager $objectManager)
    {
        $flavors = ['USDJ'=>10,'USMJ'=>5,'USBJ'=>0];

        foreach($flavors as $label => $weight) {
            $entity = new Entity\JudgeFlavor;
            $entity->setFlavor($label)->setWeight($weight);
            ${$label} = $entity;
            $objectManager->persist($entity);
        }

        $judgeHat = $objectManager->getRepository('InterpretersOffice\Entity\Hat')
            ->findOneBy(['name' => 'Judge']);

        $locations = $objectManager->getRepository(Entity\Location::class);

        $data = [
            [
                'lastname' => 'Failla',
                'firstname' => 'Katherine',
                'middlename' => '',
                'default_location'=>'618',
            ],
            [
                'lastname' => 'Daniels',
                'firstname' => 'George',
                'middlename' => 'B.',
                'default_location'=>'11A',
            ],
            [
                'lastname' => 'Dinklesnort',
                'firstname' => 'Roland',
                'middlename' => 'Z.',
                'default_location'=>'14A',
            ],
            [
                'lastname' => 'Noobieheimer',
                'firstname' => 'Susie',
                'middlename' => '',
                'default_location'=>'40 Foley',
            ],
        ];

        foreach ($data as $judge) {
            $entity = new Entity\Judge($judgeHat);
            $entity->setFlavor(${'USDJ'})
            ->setActive(true)
            ->setFirstname($judge['firstname'])
            ->setLastname($judge['lastname'])
            ->setMiddlename($judge['middlename'])
            ->setDefaultLocation($locations->findOneBy(['name'=>$judge['default_location']]));
            $objectManager->persist($entity);
        }
        // the Magistrate
        $mag = new Entity\AnonymousJudge();
        $mag->setDefaultLocation($locations->findOneBy(['name' => '5A']))
              ->setName('magistrate');
        $objectManager->persist($mag);

        $objectManager->flush();
    }
}
