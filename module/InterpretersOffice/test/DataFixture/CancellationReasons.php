<?php

namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use InterpretersOffice\Entity;

class CancellationReasons extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $objectManager)
    {
        $reasons = [
            'interpreter not required',
            'adjourned without notice',
            'defendant not produced',
            'forçe majeure',
            'party did not appear',
            'other',
            'unknown',
        ];
        foreach ($reasons as $r) {
            $e = (new Entity\ReasonForCancellation())->setReason($r);
            $objectManager->persist($e);
        }
        //printf("\nmy order is %d in %s\n",$this->getOrder(),__METHOD__);
        $objectManager->flush();
    }

    public function getOrder()
    {
        return 1;
    }
}
