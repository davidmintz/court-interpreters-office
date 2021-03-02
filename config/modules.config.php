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
/** to do: treat this like the other optional modules */
if (! getenv('TRAVIS')) {
    $modules[] = 'SDNY\Vault';
}

$optional_modules = require('module/Admin/config/optional_modules.php');
while ($module = array_search(true,$optional_modules)) {
    $modules[] = $module;
    unset($optional_modules[$module]);
}
return $modules;
