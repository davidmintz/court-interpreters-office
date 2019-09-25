<?php

/** module/Notes/src/Module.php */

namespace InterpretersOffice\Admin\Notes;

/**
 * Module class for our InterpretersOffice\Admin\Notes module.
 */
class Module {
    /**
     * returns this module's configuration.
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__.'/../config/module.config.php';
    }

    public function onBootstrap(\Zend\EventManager\EventInterface $event)
    {
        $container = $event->getApplication()->getServiceManager();
        $log = $container->get('log')
            ->debug("new module Notes is up and running");
    }
}
