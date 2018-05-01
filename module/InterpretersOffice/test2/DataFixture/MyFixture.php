<?php
namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;

use InterpretersOffice\Entity;

class MyFixture implements FixtureInterface
{

    public function load(ObjectManager $manager)
    {
        $language = (new Entity\Language())->setName('Spanish');
        $manager->persist($language);
        $manager->flush();        
    }

}
