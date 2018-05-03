<?php

namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use InterpretersOffice\Entity;
use ApplicationTest\DataFixture\EventTypeCategories;


class EventTypeCategories extends AbstractFixture
{

    public function load(ObjectManager $objectManager)
    {
        
        foreach (['in', 'out', 'n/a'] as $cat) {
            $category = new Entity\EventCategory();
            $category->setCategory($cat);
            $objectManager->persist($category);
        }
        $objectManager->flush();
    }

}
