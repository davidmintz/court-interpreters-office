<?php

// module/Application/tests/config/bootstrap.php

require (__DIR__. '/../../../../vendor/autoload.php');
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$config = require (__DIR__.'/../config/autoload/local.php');
$dbParams = $config['doctrine']['connection']['orm_default']['params']; 
$dbParams['driver'] = 'pdo_sqlite';

$entitiesPath = [
	__DIR__ .'/../../../../module/Application/src/Application/Entity/',

];

$config = Setup::createAnnotationMetadataConfiguration($entitiesPath,true,null, null, false);
return EntityManager::create($dbParams, $config);

