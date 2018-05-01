<?php
declare(strict_types=1);

namespace ApplicationTest;

use PHPUnit\Framework\TestCase;
use Doctrine\Common\DataFixtures\Loader;

use ApplicationTest\DataFixture;

use InterpretersOffice\Entity;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;



class TestTestCase extends TestCase
{

    private $loader;
    private $em;

    public function setUp()
    {
        $loader = new Loader();

        //$loader->addFixture(new DataFixture\MyFixture());
        $this->loader = $loader;
        $em = Bootstrap::getEntityManager();
        $this->em = $em;
        $loader->addFixture(new DataFixture\MyFixture($em));
        $executor = Bootstrap::getFixtureExecutor();
        $executor->execute($loader->getFixtures());
    }

    public function testSomething()
    {
        $this->assertTrue(is_object($this->loader));
        $em = $this->em;
        $repo = $em->getRepository(Entity\Language::class);
        $something = $repo->findOneBy([
            'name' => 'Spanish'
        ]);
        $this->assertInstanceOf(Entity\Language::class, $something);
    }
}
