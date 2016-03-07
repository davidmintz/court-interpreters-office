<?php 

require(__DIR__.'/../vendor/autoload.php');

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$entitiesPath = [
	//__DIR__ .'/../module/Requests/src/Requests/Entity/',
	__DIR__ .'/../module/Application/src/Application/Entity/',
	
];

$config = require(__DIR__.'/../config/autoload/doctrine.local.php');
$local = require(__DIR__.'/../config/autoload/local.php');
$params = $config['doctrine']['connection']['orm_default']['params'] + $local['doctrine']['connection']['orm_default']['params'];
$config = Setup::createAnnotationMetadataConfiguration($entitiesPath,true,null, null, false);
return EntityManager::create($params, $config);