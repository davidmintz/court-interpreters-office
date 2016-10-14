<?php

// module/Application/tests/config/bootstrap.php

$loader = require (__DIR__. '/../../../../vendor/autoload.php');

$loader->add('Application',__DIR__. '/../../../../module/Application/src');

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$config = require (__DIR__.'/../config/autoload/test.local.php');
$dbParams = $config['doctrine']['connection']['orm_default']['params']; 
$dbParams['driver'] = 'pdo_sqlite';

$entitiesPath = [
	//__DIR__ .'/../../../../module/Requests/src/Requests/Entity/',
	__DIR__ .'/../../../../module/Application/src/Entity/',
	
];

$entityConfig = Setup::createAnnotationMetadataConfiguration($entitiesPath,true,null, null, false);
return EntityManager::create($dbParams, $entityConfig);

