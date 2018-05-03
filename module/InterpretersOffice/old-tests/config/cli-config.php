<?php

use Doctrine\ORM\Tools\Console\ConsoleRunner;

// replace with file to your own project bootstrap
$entityManager = require __DIR__.'/bootstrap.php';

// replace with mechanism to retrieve EntityManager in your app
//echo __FILE__ .  " is fucking running\n";exit();
return ConsoleRunner::createHelperSet($entityManager);
