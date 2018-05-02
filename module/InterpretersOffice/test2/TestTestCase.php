<?php
declare(strict_types=1);

namespace ApplicationTest;

use PHPUnit\Framework\TestCase;
use Doctrine\Common\DataFixtures\Loader;

use ApplicationTest\DataFixture;



use InterpretersOffice\Entity;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class TestTestCase extends TestCase
{

    //private $loader;

    /** @var \Doctrine\ORM\EntityManager */
    private $em;

    public function setUp()
    {


        $em = Bootstrap::getEntityManager();
        $this->em = $em;
        // to make the ids start over, if we care enough...
        /*
        $pdo = $em->getConnection()->getWrappedConnection();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE languages; TRUNCATE TABLE locations; TRUNCATE TABLE location_types;');
        $pdo->exec('TRUNCATE TABLE hats; TRUNCATE TABLE roles;');
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1;');
        */
        $executor = Bootstrap::getFixtureExecutor();

        $executor->execute([
            new DataFixture\CancellationReasons(),
            new DataFixture\Languages(),
            new DataFixture\Roles(),
            new DataFixture\Hats(),
            new DataFixture\Locations(),
            new DataFixture\EventTypeCategories(),
            new DataFixture\EventTypes(),
            new DataFixture\DefendantNames(),
            new DataFixture\Judges(),
            new DataFixture\Interpreters(),
            new DataFixture\Users(),
        ]);
    }

    public function testOtherThing()
    {

        $repository = $this->em->getRepository('InterpretersOffice\Entity\LocationType');
        $this->assertTrue(is_object($repository));
        $shit = $repository->findOneBy(['type' => 'courthouse']);
        $this->assertTrue(is_object($shit));
        $hat_repo = $this->em->getRepository(Entity\Hat::class);
        $hats = $hat_repo->findAll();
        $this->assertTrue(count($hats) > 0);
        $defts_repo =  $this->em->getRepository(Entity\DefendantName::class);
        $this->assertTrue(count($defts_repo->findAll()) > 0);

    }

    public function testSomething()
    {
        $this->assertTrue(true);
        $em = $this->em;
        $repo = $em->getRepository(Entity\Language::class);
        $something = $repo->findOneBy([
            'name' => 'Spanish'
        ]);
        $this->assertInstanceOf(Entity\Language::class, $something);
    }


}
