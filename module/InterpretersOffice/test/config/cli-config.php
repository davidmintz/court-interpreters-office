<?php

use Doctrine\ORM\Tools\Console\ConsoleRunner;

// replace with file to your own project bootstrap
$entityManager = require __DIR__.'/bootstrap.php';

// replace with mechanism to retrieve EntityManager in your app

return ConsoleRunner::createHelperSet($entityManager);
