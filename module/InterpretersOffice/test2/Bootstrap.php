<?php
/** module/InterpretersOffice/test2/Bootstrap.php */

namespace ApplicationTest;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;

class Bootstrap
{
    private static $entityManager;

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

}
