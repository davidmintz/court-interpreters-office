<?php
/** module/InterpretersOffice/test2/config/bootstrap.php */

$app_root = realpath(__DIR__.'/../../../../');
require("$app_root/vendor/autoload.php");
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$paths = ["$app_root/module/InterpretersOffice/src/Entity/"];

/*
// we (once) couldn't get someshit to work, but this seemed to work:
$path = __DIR__.'/../../../../vendor/zendframework/zend-form/src/Annotation/';
$files = glob("$path/*php");
foreach ($files as $file) {
    AnnotationRegistry::registerFile($file);
}
*/
AnnotationRegistry::registerLoader('class_exists');
$params = (require(__DIR__.'/autoload/doctrine.test.php'))
    ['doctrine']['connection']['orm_default']['params'];
$config = Setup::createAnnotationMetadataConfiguration($paths,
    true, null, null, false);
$em = EntityManager::create($params, $config);
return $em;
