<?php
/**
 * config/modules.config.php
 */

/**
 * List of enabled modules for this application.
 *
 * This should be an array of module namespaces used in the application.
 */
$modules = [
    'Laminas\Session',
    'Laminas\Mvc\Plugin\FlashMessenger',
    'Laminas\ZendFrameworkBridge',
    'DoctrineModule',
    'DoctrineORMModule',
    'Laminas\Form',
    'Laminas\Router',
    'Laminas\Navigation',
    'InterpretersOffice',
    'InterpretersOffice\Admin',
    
];
// some modules are optional, so we load the appropriate 
// configuration file depending on the environment
$env = getenv('environment');
$config = require(__DIR__."/autoload/local.{$env}.php");

if (isset($config['optional_modules'])) {
    array_push($modules, ...$config['optional_modules']);
}

if (! getenv('TRAVIS')) {
    $modules[] = 'SDNY\Vault';
}

return $modules;
