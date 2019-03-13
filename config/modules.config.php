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
    'Zend\Session',
    'Zend\Mvc\Plugin\FlashMessenger',
    'DoctrineModule',
    'DoctrineORMModule',
    'Zend\Form',// yes necessary
    'Zend\Router',
    'Zend\Navigation',
    'InterpretersOffice',
    'InterpretersOffice\Admin',
    'InterpretersOffice\Requests',
    'Sandbox'
];
if (! getenv('TRAVIS')) {
    $modules[] = 'SDNY\Vault';
}
return $modules;
