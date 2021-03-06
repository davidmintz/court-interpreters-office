<?php

/** config/doctrine-bootstrap.php */

/** @var Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../vendor/autoload.php';

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

use InterpretersOffice\Entity\Listener;
use InterpretersOffice\Entity\Listener\EventEntityListener;

// does not work:
// AnnotationRegistry::registerAutoloadNamespace(
//     'Laminas\Form\Annotation',
//     __DIR__.'/../vendor/laminas/laminas-form/src/Annotation'
// );
///* does work:
$path = __DIR__.'/../vendor/laminas/laminas-form/src/Annotation';
$files = glob("$path/*php");

foreach ($files as $file) {
    if (basename($file) == 'Object.php') { continue; }
    AnnotationRegistry::registerFile($file);
}
//*/

$params = require 'autoload/doctrine.local.php';
$dbParams = $params['doctrine']['connection']['orm_default']['params'];
$entitiesPath = [
    __DIR__.'/../module/InterpretersOffice/src/Entity/',
    __DIR__.'/../module/Requests/src/Entity/',
    __DIR__.'/../module/Notes/src/Entity/',
    __DIR__.'/../module/Rotation/src/Entity/',
];
$config = Setup::createAnnotationMetadataConfiguration($entitiesPath, true, null, null, false);
$em = EntityManager::create($dbParams, $config);

$listener = new Listener\InterpreterEntityListener();
$eventManager = new Laminas\EventManager\EventManager(new Laminas\EventManager\SharedEventManager());
$listener->setEventManager($eventManager);

$logger = new Laminas\Log\Logger;
$writer = new Laminas\Log\Writer\Stream('php://output');
$logger->addWriter($writer);
$listener->setLogger($logger);

$resolver = $em->getConfiguration()->getEntityListenerResolver();
$resolver->register($listener);

return $em;

/*
When running orm:run-dql:
We have discovered that if you do not set --depth 2 or at most 3, it will seem
to hang, and if you were to wait long enough, maybe it would run out of memory.
My guess: Doctrine run-dql by default tries to fetch ~everything~ by way of
related entities, and relations of relations, etc., unless you specify otherwise.


// and another one?
//$listener = new Listener\EventEntityListener();
//$listener->setEventManager($eventManager);
//$resolver->register($listener);


// a (temporary?) fix for entity listener error that happens when we try to
// call methods on its logger instance which is set by the service manager that
// does not exist in this CLI environment

*/
