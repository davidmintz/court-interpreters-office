<?php

namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use InterpretersOffice\Entity;

class JudgeLoader implements FixtureInterface
{
    public function load(ObjectManager $objectManager)
    {
        $judgeHat = $objectManager->getRepository('InterpretersOffice\Entity\Hat')
            ->findOneBy(['name' => 'Judge']);
        $failla = new Entity\Judge($judgeHat);
        $usdj = $objectManager->getRepository('InterpretersOffice\Entity\JudgeFlavor')
            ->findOneBy(['flavor' => 'USDJ']);
        $locations = $objectManager->getRepository(Entity\Location::class);
        $courtroom_618 = $locations->findOneBy(['name' => '618']);


        $failla
            ->setHat($judgeHat)
            ->setFlavor($usdj)
            ->setActive(true)
            ->setFirstname('Katherine')
            ->setLastname('Failla');
        $failla->setDefaultLocation($courtroom_618);
        $objectManager->persist($failla);

        $daniels = new Entity\Judge($judgeHat);
        $daniels->setFlavor($usdj)
            ->setFirstname('George')
            ->setLastname('Daniels')
            ->setActive(true)
            ->setDefaultLocation(
                $objectManager->getRepository('InterpretersOffice\Entity\Location')
                        ->findOneBy(['name' => '11A'])
            );
         $objectManager->persist($daniels);
        // a few make-believe
        $dinklesnort = new Entity\Judge($judgeHat);
        $dinklesnort->setFlavor($usdj)
            ->setFirstname('Roland')
            ->setMiddleName('Z.')
            ->setLastname('Dinklesnort')
            ->setActive(true)
            ->setDefaultLocation($locations->findOneBy(['name' => '14A']));
        $objectManager->persist($dinklesnort);

        // the Magistrate
        $mag = new Entity\AnonymousJudge();
        $mag->setDefaultLocation($locations->findOneBy(['name' => '5A']))
              ->setName('magistrate');
        $objectManager->persist($mag);
        $objectManager->flush();
    }
}
