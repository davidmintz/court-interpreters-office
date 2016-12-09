<?php
/**
 * for using Doctrine from the command line
 * 
 */
use Doctrine\ORM\Tools\Console\ConsoleRunner;

// replace with file to your own project bootstrap
$entityManager = require(__DIR__.'/doctrine-bootstrap.php');

// replace with mechanism to retrieve EntityManager in your app

return ConsoleRunner::createHelperSet($entityManager);