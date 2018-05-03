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
    AnnotationRegistry::registerFile($file);
}

$config = require __DIR__.'/../config/autoload/doctrine.test.php';
$dbParams = $config['doctrine']['connection']['orm_default']['params'];
$dbParams['driver'] = 'pdo_mysql';

$entitiesPath = [
    //__DIR__ .'/../../../../module/Requests/src/Requests/Entity/',
    __DIR__.'/../../../../module/InterpretersOffice/src/Entity/',

];

$entityConfig = Setup::createAnnotationMetadataConfiguration($entitiesPath, true, null, null, false);

return EntityManager::create($dbParams, $entityConfig);
