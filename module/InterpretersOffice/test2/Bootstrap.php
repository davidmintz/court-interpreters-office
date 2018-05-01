<?php
/** module/InterpretersOffice/test2/Bootstrap.php */

namespace ApplicationTest;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

class Bootstrap
{
    private static $entityManager;

    private static $fixtureExecutor;


    static public function getEntityManager()
    {
        if (self::$entityManager) {
            return self::$entityManager;
        }
        $em = require(__DIR__.'/config/bootstrap.php');
        self::$entityManager = $em;

        return $em;

    }

    static public function getFixtureExecutor()
    {
        if (self::$fixtureExecutor) {
            return $fixtureExecutor;
        }
        $purger = new ORMPurger();
        $executor = new ORMExecutor(self::getEntityManager(), $purger);
        self::$fixtureExecutor = $executor;
        return $executor;
    }

}
