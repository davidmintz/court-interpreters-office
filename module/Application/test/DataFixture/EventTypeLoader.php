<?php

namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;

use Application\Entity;


class EventTypeLoader implements FixtureInterface {
    
    public function load(ObjectManager $objectManager)
    {
        
        $categories = ['in','out','n/a'];
        
        foreach ($categories as $cat) {
            $category = new Entity\EventCategory;
            $category->setCategory($cat);
            $objectManager->persist($category);
        }
        $objectManager->flush();
        
        $in = $objectManager->getRepository('Application\Entity\EventCategory')
                ->findOneBy(['category'=>'in']);
        
        $out =  $objectManager->getRepository('Application\Entity\EventCategory')
                ->findOneBy(['category'=>'out']);
        $types  = [
            ['pretrial conference','in'],
            ['sentence','in'],
            ['attorny/client interview','out'],
            ['plea','in'],
            ['presentment','in'],
            ['arraignment','in'],
            ['pretrial services intake','out'],
            ['probation PSI interview','out'],
        ];
        foreach ($types as $type) {
            
            $entity = new Entity\EventType();
            $entity
                    ->setCategory(${$type[1]})
                    ->setName($type[0])
                    ->setComments('');
            $objectManager->persist($entity);
        }
      
        $objectManager->flush();
    }
}
