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
    'Laminas\Form',// yes necessary
    'Laminas\Router',
    'Laminas\Navigation',
    'InterpretersOffice',
    'InterpretersOffice\Admin',
    'InterpretersOffice\Admin\Notes',
    'InterpretersOffice\Admin\Rotation',
    'InterpretersOffice\Requests',
    'Sandbox'
];
if (! getenv('TRAVIS')) {
    $modules[] = 'SDNY\Vault';
}
return $modules;
