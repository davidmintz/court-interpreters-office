<?php

namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use InterpretersOffice\Entity;

class JudgeLoader implements FixtureInterface
{
    public function load(ObjectManager $objectManager)
    {
        $failla = new Entity\Judge();
        $usdj = $objectManager->getRepository('InterpretersOffice\Entity\JudgeFlavor')
            ->findOneBy(['flavor' => 'USDJ']);
        $courtroom_618 = $objectManager->getRepository('InterpretersOffice\Entity\Location')
            ->findOneBy(['name' => '618']);

        $judgeHat = $objectManager->getRepository('InterpretersOffice\Entity\Hat')
            ->findOneBy(['name' => 'Judge']);
        $failla
            ->setHat($judgeHat)
            ->setFlavor($usdj)
            ->setActive(true)
            ->setFirstname('Katherine')
            ->setLastname('Failla');
        $failla->setDefaultLocation($courtroom_618);
        $objectManager->persist($failla);

        $daniels = new Entity\Judge();
        $daniels->setHat($judgeHat)
            ->setFlavor($usdj)
            ->setFirstname('George')
            ->setLastname('Daniels')
            ->setActive(true)
            ->setDefaultLocation(
                   $objectManager->getRepository('InterpretersOffice\Entity\Location')
                        ->findOneBy(['name' => '11A'])
             );

        $objectManager->persist($daniels);
        $objectManager->flush();
    }
}
