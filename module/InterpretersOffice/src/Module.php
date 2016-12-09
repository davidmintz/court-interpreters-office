<?php
/**
 * module/InterpretersOffice/src/Module.php.
 */

namespace InterpretersOffice;

//use Zend\Mvc\MvcEvent;

/**
 * Module class for application's main module.
 */
class Module
{
    const VERSION = '3.0.2dev';

    /**
     * returns this module's configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__.'/../config/module.config.php';
    }

    //public function onBootstrap(MvcEvent $event) { }
}
