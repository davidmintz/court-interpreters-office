<?php

namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use InterpretersOffice\Entity\Language;

class Languages extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $languages = [
            ['name' => 'Spanish'],
            ['name' => 'Foochow'],
            ['name' => 'Russian'],
            ['name' => 'Cantonese'],
            ['name' => 'Mandarin'],
            ['name' => 'Arabic'],
            ['name' => 'French'],
            ['name' => 'Italian'],

        ];
        foreach ($languages as $language) {
            $entity = new Language();
            $entity->setName($language['name'])->setComments('');
            $manager->persist($entity);
        }
        $manager->flush();
    }

    /**
     * implements OrderedFixtureInterface
     * doesn't seem to work. I'm not impressed.
     */
    public function getOrder()
    {
        return 2;
    }
}
