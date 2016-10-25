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

final class FixtureManager
{
    /**
     * Get EntityManager
     * @return Doctrine\ORM\EntityManager
     */
    public static function getEntityManager()
    {
        
        $paths = [
            'module/Application/src',
          //  __DIR__.'/../../../Requests/src/Requests/Entity',

        ];
        $isDevMode = true;
        $connectionParams = array(
            'driver'   => 'pdo_sqlite',
            //'user'     => $config['user'],
            //'password' => $config['password'],
            'path'   =>'module/Application/test/data/office.sqlite',
        );
        
        $config = Setup::createConfiguration($isDevMode);
        $driver = new AnnotationDriver(new AnnotationReader(), $paths);

        AnnotationRegistry::registerLoader('class_exists');
        $config->setMetadataDriverImpl($driver);

        $entityManager = EntityManager::create($connectionParams, $config);

        return $entityManager;
    }

    /**
     * Drop tables and Create tables
     */
    public static function start()
    {

        $schemaTool = new SchemaTool(static::getEntityManager());
        $metadatas  = static::getEntityManager()
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
