<?php
/**
 * for using Doctrine from the command line.
 */
use Doctrine\ORM\Tools\Console\ConsoleRunner;

// replace with file to your own project bootstrap
$entityManager = require __DIR__.'/doctrine-bootstrap.php';

$config = $entityManager->getConfiguration();

// try to make Doctrine cli clear cache, shit does not work:
// $directory = __DIR__.'/../data/DoctrineModule/cache';
// $config->setResultCacheImpl( new Doctrine\Common\Cache\FilesystemCache($directory));

return ConsoleRunner::createHelperSet($entityManager);
