<?php

namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;

use InterpretersOffice\Entity;

/**
* loads test fixture data for defendant names
*
*/


class DefendantNameLoader implements FixtureInterface {

    public function load(ObjectManager $objectManager)
    {
        
        $names = [
            ['Alguien','Juan'],
            ['Fulano Mengano','Joaquín'],
            ['Snyertzski','Boris'],
            ['Ajmanabalahadji','Mohammed'],
            ['Zheng','Xao Xui']

        ];
        foreach ($names as $name) {

            $entity = (new Entity\DefendantName())->setFullname(
                $name[0],$name[1]
            );
            $objectManager->persist($entity);
        }
        $objectManager->flush();
    }

  }
