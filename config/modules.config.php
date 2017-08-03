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
    'Zend\Log',
    'Zend\Mvc\Plugin\FlashMessenger',
    'Zend\Session',
    'DoctrineModule',
    'DoctrineORMModule',
    //'Zend\Cache',
    'Zend\Form',
    //'Zend\InputFilter',
    //'Zend\Filter',
    //'Zend\Paginator',
    //'Zend\Hydrator',
    'Zend\Router',
    //'Zend\Validator',
    'Zend\Navigation',
    'InterpretersOffice',
    'InterpretersOffice\Admin',
    'InterpretersOffice\Requests',
    
];
if (! getenv('TRAVIS')) {
    $modules[] = 'SDNY\Vault';
}
return $modules;
