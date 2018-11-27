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
use InterpretersOffice\Entity\Listener\EventEntityListener;
use Zend\Authentication\Result;
use Zend\Authentication\AuthenticationServiceInterface;
use ApplicationTest\FakeAuth;
use ApplicationTest\FixtureSetupTest;

/**
 * a concrete implementation of AbstractControllerTest
 * for the sole purpose of allowing us to instantiate it,
 * call its setup() method, and get the DI container
 */
class SetupHelper extends AbstractControllerTest
{
}

/**
 * we seem to need this to get us past FixtureSetupTest
 */
class FakeAuth implements \Zend\Authentication\AuthenticationServiceInterface
{
        public function hasIdentity()
        {
            return true;
        }
        public function getIdentity()
        {
            return (object)[
                'username'=> 'david',
                'id' => 117,
            ];
        }
        public function authenticate()
        {
            return new ZendAuthenticationResult(1, $this->getIdentity());
        }

        public function clearIdentity()
        {
        }
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
        $config = (include(__DIR__.'/config/autoload/doctrine.test.php'))
            ['doctrine']['connection']['orm_default']['params'];
        $connectionParams = [
            'driver'   => $config['driver'],
            'user'     => $config['user'],
            'password' => $config['password'],
            'dbname'   => $config['dbname'],
            'path' => 'module/InterpretersOffice/test/data/office.sqlite',
        ];

        $config = Setup::createConfiguration($isDevMode);
        $driver = new AnnotationDriver(new AnnotationReader(), $paths);

        AnnotationRegistry::registerLoader('class_exists');
        $config->setMetadataDriverImpl($driver);

        $entityManager = EntityManager::create($connectionParams, $config);
        $helper = new SetupHelper();
        $helper->setUp();
        /** @var Zend\ServiceManager\ServiceManager $container */
        $container = $helper->getApplicationServiceLocator();
        $listener = $container->get('interpreter-listener');
        $event_entity_listener = $container->get(Listener\EventEntityListener::class);
        $event_entity_listener->setAuth(new FakeAuth());
        $resolver = $entityManager->getConfiguration()->getEntityListenerResolver();
        $resolver->register($listener);

        // looks like we need to be authenticated before EventListenerFactory
        // injects auth in ScheduleUpdateManager, hence...

        $auth = new FakeAuth();
        $container->get('InterpretersOffice\Admin\Service\ScheduleUpdateManager')
            ->setAuth($auth);
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
    public static function dataSetup(Array $more = null)
    {
        $executor = self::getFixtureExecutor();

        $fixtures = [
            new DataFixture\LanguageLoader(),
            new DataFixture\HatLoader(),
            new DataFixture\EventTypeLoader(),
            new DataFixture\LocationLoader(),
            new DataFixture\DefendantLoader(),
            new DataFixture\JudgeLoader(),
            new DataFixture\InterpreterLoader(),
            new DataFixture\CancellationReasonLoader(),
            new DataFixture\UserLoader(),
            new DataFixture\EventLoader(),
        ];
        if ($more) {
            foreach ($more as $fixture) {
                $fixtures[] = $fixture;
            }
        }
        $executor->execute($fixtures);
    }
}
