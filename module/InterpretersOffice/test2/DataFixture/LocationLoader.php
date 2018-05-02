<?php

namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use InterpretersOffice\Entity\Language;

class LanguageLoader implements FixtureInterface
{
    public function load(ObjectManager $objectManager)
    {

        $repository = $objectManager->getRepository('InterpretersOffice\Entity\LocationType');
        $parentlocations = [
            // name,    type,   parent
            ['500 Pearl',  $repository->findOneBy(['type' => 'courthouse']), null],
            ['40 Foley',  $repository->findOneBy(['type' => 'courthouse']), null],
        ];
        foreach ($parentlocations as $p) {
            $locationEntity = new Entity\Location();
            $locationEntity->setName($p[0])->setType($p[1])->setParentLocation($p[2])->setComments('');
            $objectManager->persist($locationEntity);
        }
        $objectManager->flush();

    }

}
