<?php

namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use InterpretersOffice\Entity\LanguageCredential;

class LanguageCredentialLoader implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $creds = [
            ['name' => 'A0-certified','abbreviation'=> "AO",'description'=>"federal certification bla bla"],
            ['name' => 'Professionally Qualified','abbreviation'=> "PS",'description'=>"AO yadda yadda bla bla"],
            ['name' => 'Language-Skilled','abbreviation'=> "LS",'description'=>"federal certification bla bla"],
        ];
        foreach ($creds as $cred) {
            $entity = new LanguageCredential();
            $entity
                ->setName($cred['name'])
                ->setAbbreviation($cred['abbreviation'])
                ->setDescription($cred['description']);
            $manager->persist($entity);
        }
        $manager->flush();
    }
}
