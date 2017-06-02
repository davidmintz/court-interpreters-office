<?php

/** config/doctrine-bootstrap.php */

/** @var Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../vendor/autoload.php';

//use Doctrine\Common\Annotations\FileCacheReader;
//use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

//$reader = new FileCacheReader( new AnnotationReader(), __DIR__.'/../data/cache',$debug = true);
//$reader = new AnnotationReader();

// does not work:
AnnotationRegistry::registerAutoloadNamespace(
    'Zend\Form\Annotation',
    __DIR__.'/../vendor/zendframework/zend-form/src/Annotation'
);
/* does work: */
$path = __DIR__.'/../vendor/zendframework/zend-form/src/Annotation/';
$files = glob("$path/*php");
foreach ($files as $file) {
    AnnotationRegistry::registerFile($file);
}

$params = require 'autoload/doctrine.local.php';
$dbParams = $params['doctrine']['connection']['orm_default']['params'];
$entitiesPath = [
    __DIR__.'/../module/InterpretersOffice/src/Entity/',
];
$config = Setup::createAnnotationMetadataConfiguration($entitiesPath, true, null, null, false);
$em = EntityManager::create($dbParams, $config);
$listener = new InterpretersOffice\Entity\Listener\InterpreterEntityListener();
$listener->setEventManager(new Zend\EventManager\EventManager(new Zend\EventManager\SharedEventManager()));
$em->getConfiguration()->getEntityListenerResolver()->register($listener);
return $em;
