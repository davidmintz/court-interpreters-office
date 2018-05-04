<?php
namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use InterpretersOffice\Entity;
use ApplicationTest\DataFixture\EventTypeCategories;


class LocationTypes extends AbstractFixture
{

    public function load(ObjectManager $objectManager)
    {

        foreach (['courthouse','courtroom','holding cell','interpreters office',
                'jail','Pretrial Services office','public area',
                'US Probation office',]
        as $type) {
            $entity = new Entity\LocationType();
            $entity->setType($type)->setComments('');
            $objectManager->persist($entity);
        }
        $objectManager->flush();
    }
}
