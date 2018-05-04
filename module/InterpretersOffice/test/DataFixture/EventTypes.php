<?php

namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use InterpretersOffice\Entity;

class EventTypes extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $objectManager)
    {

        //(new EventTypeCategories())->load($objectManager);

        $repo = $objectManager->getRepository('InterpretersOffice\Entity\EventCategory');
        $in = $repo->findOneBy(['category' => 'in']);
        $out = $repo->findOneBy(['category' => 'out']);
        $types = [
            ['pretrial conference', 'in'],
            ['sentence', 'in'],
            ['attorney/client interview', 'out'],
            ['plea', 'in'],
            ['conference', 'in'],
            ['presentment', 'in'],
            ['arraignment', 'in'],
            ['pretrial services intake', 'out'],
            ['probation PSI interview', 'out'],
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

    public function getDependencies()
    {
        printf("\nfuckin shit is running: %s\n",__METHOD__);
        return [ 'EventTypeCategories' ];
    }
}
