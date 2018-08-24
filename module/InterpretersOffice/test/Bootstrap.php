<?php

namespace ApplicationTest;

class Bootstrap
{

    /**
     * entity manager
     *
     * @var \Doctrine\ORM\EntityManager
     */
    private static $entityManager;

    /**
     * fixture executor
     *
     * @var \Doctrine\Common\DataFixtures\Executor\ORMExecutor
     */
    private static $fixtureExecutor;


    /**
     * gets entity manager
     *
     * @return \Doctrine\ORM\EntityManager
     */
    static public function getEntityManager()
    {
        if (self::$entityManager) {
            return self::$entityManager;
        }
        $em = require(__DIR__.'/config/bootstrap.php');
        self::$entityManager = $em;

        return $em;
    }

    /**
     * gets fixture manager
     *
     * @return \Doctrine\Common\DataFixtures\Executor\ORMExecutor
     */
    static public function getFixtureExecutor()
    {
        if (self::$fixtureExecutor) {
            return self::$fixtureExecutor;
        }
        $purger = new ORMPurger();
        $executor = new ORMExecutor(self::getEntityManager(), $purger);
        self::$fixtureExecutor = $executor;
        return $executor;
    }

    /**
     * loads fixtures
     *
     * wraps ORMExecutor::load inside SET FOREIGN_KEY_CHECKS because we could
     * not come up with any other way to beat the foreign key constraint errors
     * brought about by Location entity's self-referencing foreign keys
     *
     * @var \Doctrine\Common\DataFixtures\FixtureInterface[]
     *
     * @return void
     */
    static public function load(Array $fixtures)
    {
        $executor = self::getFixtureExecutor();
        $em = self::getEntityManager();
        $pdo = $em->getConnection()->getWrappedConnection();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        $executor->execute($fixtures);
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

    }

}
putenv('environment=testing');
/*
FixtureManager::start();
$script = __DIR__.'/data/sqlite.schema.sql';
$db = FixtureManager::getEntityManager()->getConnection();
$shit = $db->executeQuery(file_get_contents($script));
etc
 */
