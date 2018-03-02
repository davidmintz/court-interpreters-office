<?php
/**
 * module/Vault/src/Module.php.
 */

namespace SDNY\Vault;

/**
 * Module class for SDNY\Vault module.
 */
class Module
{

    /**
     * returns this module's configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__.'/../config/module.config.php';
    }
    /**
     * for now, just making sure it's working
     *
     * @param \Zend\Mvc\MvcEvent $event
     */
    public function onBootstrap(\Zend\Mvc\MvcEvent $event)
    {
        //$config = $event->getApplication()->getServiceManager()->get('config')['vault'];
    }
}
