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

/**
 * not such a good idea after all? the idea was to register
 * the Interpreter entity listener in the test environment. the
 * result is that the authentication does not seem to persist as it
 * should in our tests. how this is related, I have no fucking clue.
 */
class SetupHelper extends AbstractControllerTest {}


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
        ///* // see above
        $helper = new SetupHelper();
        $helper->setUp();
        $container = $helper->getApplicationServiceLocator();
        $listener = $container->get('interpreter-listener');
        $resolver = $entityManager->getConfiguration()->getEntityListenerResolver();
        $resolver->register($listener);
        $event_listener_fqcn = \InterpretersOffice\Entity\Listener\EventEntityListener::class;
        $resolver->register($container->get($event_listener_fqcn));
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
}
