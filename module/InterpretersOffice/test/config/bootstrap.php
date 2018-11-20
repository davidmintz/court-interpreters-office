<?php

// module/Application/tests/config/bootstrap.php

$loader = require __DIR__.'/../../../../vendor/autoload.php';

$loader->add('Application', __DIR__.'/../../../../module/InterpretersOffice/src');
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

//we could not get namespace autoloading to work, but this does work:
$path = __DIR__.'/../../../../vendor/zendframework/zend-form/src/Annotation/';
$files = glob("$path/*php");
foreach ($files as $file) {
    if (basename($file) == 'Object.php') { continue; }
    AnnotationRegistry::registerFile($file);
}

$config = require __DIR__.'/../config/autoload/doctrine.test.php';
$dbParams = $config['doctrine']['connection']['orm_default']['params'];
$dbParams['driver'] = 'pdo_sqlite';

$entitiesPath = [
    __DIR__.'/../../../InterpretersOffice/src/Entity/',
    __DIR__ .'/../../../Requests/src/Entity/',

];

$entityConfig = Setup::createAnnotationMetadataConfiguration($entitiesPath, true, null, null, false);

return EntityManager::create($dbParams, $entityConfig);
