<?php
declare(strict_types=1);

namespace ApplicationTest;

use PHPUnit\Framework\TestCase;
use Doctrine\Common\DataFixtures\Loader;

use ApplicationTest\DataFixture;

use ApplicationTest\DataFixture\LanguageLoader;
use ApplicationTest\DataFixture\LocationLoader;

use InterpretersOffice\Entity;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;



class TestTestCase extends TestCase
{

    //private $loader;

    /** @var \Doctrine\ORM\EntityManager */
    private $em;

    public function setUp()
    {

        $em = Bootstrap::getEntityManager();
        $this->em = $em;
        // this will make the ids start over, if we care enough...
        $pdo = $em->getConnection()->getWrappedConnection();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0;TRUNCATE TABLE languages');
        //$loader->addFixture(new DataFixture\LanguageLoader($em));
        $executor = Bootstrap::getFixtureExecutor();
        $executor->execute([
            //new LocationLoader(),
            new LanguageLoader(),
        ]);
    }

    public function testOtherThing()
    {

        $repository = $this->em->getRepository('InterpretersOffice\Entity\LocationType');
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

    public function testSomething()
    {
        $this->assertTrue(true);
        $em = $this->em;
        $repo = $em->getRepository(Entity\Language::class);
        $something = $repo->findOneBy([
            'name' => 'Spanish'
        ]);
        //printf("\nshit is: %s\n",gettype($something));
        $this->assertInstanceOf(Entity\Language::class, $something);
    }


}
