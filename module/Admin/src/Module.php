<?php
/**
 * module/Admin/src/Module.php. experimental.
 *
 */

namespace InterpretersOffice\Admin;

/**
 * Module class for our Admin module
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
     * {@inheritDoc}
     */
    public function onBootstrap(\Zend\EventManager\EventInterface $event)
    {
        // $shit =  $event->getTarget()->getServiceManager();
        // echo "woo hoo! .... ";
        // echo get_class($shit->get('auth'));
    }
}