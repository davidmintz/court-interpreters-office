<?php
/**
 * module/Requests/src/Module.php.
 */

namespace InterpretersOffice\Requests;

/**
 * Module class for application's Requests module.
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

    public function onBootstrap(\Zend\EventManager\EventInterface $event)
    {
        $sharedEvents = $event->getApplication()->getServiceManager()->get('SharedEventManager');
        $log = $event->getApplication()->getServiceManager()->get('log');
        $sharedEvents->attach(
            'InterpretersOffice\Admin\Controller\EventsController',
            'pre.populate',
            function($e) use ($log){
                $log->debug(sprintf(
                    'running "pre.populate" event listener in %s',__CLASS__
                ));
            }
        );
    }
}
