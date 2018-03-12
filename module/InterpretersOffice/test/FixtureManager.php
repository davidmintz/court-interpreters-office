<?php

namespace ApplicationTest;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\SchemaTool;

use InterpretersOffice\Entity\Listener;

/**
 * a concrete implementation of AbstractControllerTest
 * for the sole purpose of allowing us to instantiate it,
 * call its setup() method, and get the DI container
 */
class SetupHelper extends AbstractControllerTest
{
}


final class FixtureManager
{
    /**
     * Get EntityManager.
     *
     * @return Doctrine\ORM\EntityManager
     */
    public static function getEntityManager()
    {
        $paths = [
            'module/InterpretersOffice/src',
          //  __DIR__.'/../../../Requests/src/Requests/Entity',

        ];
        $isDevMode = true;
        $connectionParams = [
            'driver' => 'pdo_sqlite',
            //'user'     => $config['user'],
            //'password' => $config['password'],
            'path' => 'module/InterpretersOffice/test/data/office.sqlite',
        ];

        $config = Setup::createConfiguration($isDevMode);
        $driver = new AnnotationDriver(new AnnotationReader(), $paths);

        AnnotationRegistry::registerLoader('class_exists');
        $config->setMetadataDriverImpl($driver);

        $entityManager = EntityManager::create($connectionParams, $config);
        $helper = new SetupHelper();
        $helper->setUp();
        $container = $helper->getApplicationServiceLocator();
        $listener = $container->get('interpreter-listener');
        $resolver = $entityManager->getConfiguration()->getEntityListenerResolver();
        $resolver->register($listener);
        $resolver->register($container->get(Listener\EventEntityListener::class));
        $resolver->register($container->get(Listener\UpdateListener::class));

        return $entityManager;
    }

    /**
     * Drop tables and Create tables.
     */
    public static function start()
    {
        $schemaTool = new SchemaTool(static::getEntityManager());
        $metadatas = static::getEntityManager()
                        ->getMetadataFactory()
                        ->getAllMetadata();
        $schemaTool->dropSchema($metadatas);
        $schemaTool->createSchema($metadatas);

        //echo "\nexiting start() in ".__CLASS__."\n";
    }

    /**
     * @return ORMExecutor
     */
    public static function getFixtureExecutor()
    {
        return new ORMExecutor(
            static::getEntityManager(),
            new ORMPurger(static::getEntityManager())
        );
    }

    /**
     * loads enough data to test events controller
     */
    public static function dataSetup()
    {
        $executor = self::getFixtureExecutor();
        $executor->execute([

            new DataFixture\LanguageLoader(),
            new DataFixture\HatLoader(),
            new DataFixture\EventTypeLoader(),
            new DataFixture\LocationLoader(),
            new DataFixture\DefendantNameLoader(),
            new DataFixture\JudgeLoader(),
            new DataFixture\InterpreterLoader(),
            new DataFixture\CancellationReasonLoader(),
            new DataFixture\UserLoader(),
            new DataFixture\EventLoader(),

        ]);
    }
}
