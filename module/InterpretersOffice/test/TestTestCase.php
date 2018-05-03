<?php
declare(strict_types=1);

namespace ApplicationTest;

use PHPUnit\Framework\TestCase;

use ApplicationTest\DataFixture;



use InterpretersOffice\Entity;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class TestTestCase extends TestCase
{

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
            new DataFixture\Events(),
        ]);
    }

    public function testFixtureInitialization()
    {
        $repository = $this->em->getRepository(Entity\LocationType::class);
        $this->assertTrue(is_object($repository));
        $shit = $repository->findOneBy(['type' => 'courthouse']);
        $this->assertTrue(is_object($shit));
        $hat_repo = $this->em->getRepository(Entity\Hat::class);
        $hats = $hat_repo->findAll();
        $this->assertTrue(count($hats) > 0);
        $defts_repo =  $this->em->getRepository(Entity\DefendantName::class);
        $this->assertTrue(count($defts_repo->findAll()) > 0);
        $languages = $this->em->getRepository(Entity\Language::class);

        $spanish = $languages->findOneBy(['name' => 'Spanish']);
        $this->assertInstanceOf(Entity\Language::class, $spanish);
        $all_languages = $languages->findAll();
        $this->assertTrue(is_array($all_languages));
        $this->assertGreaterThan(1,count($all_languages));
        $repo = $this->em->getRepository(Entity\Event::class);
        $events = $repo->findAll();
        $this->assertTrue(is_array($events));
        $this->assertGreaterThan(0,count($events));
        /** @var Entity\Event $entity */
        $entity = $events[0];
        $this->assertTrue(is_object($entity));
        $somebody = $entity->getDefendantNames()->current();
        $this->assertInstanceOf(Entity\DefendantName::class,$somebody);
        $interpreter = $entity->getInterpreterEvents()->current()->getInterpreter();
        $this->assertInstanceOf(Entity\Interpreter::class,$interpreter);

    }
}
