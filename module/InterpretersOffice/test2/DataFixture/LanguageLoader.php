<?php

namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use InterpretersOffice\Entity\Language;

class LanguageLoader implements FixtureInterface
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

        ];
        foreach ($languages as $language) {
            $entity = new Language();
            $entity->setName($language['name'])->setComments('');
            $manager->persist($entity);
        }
        $manager->flush();
    }
}
