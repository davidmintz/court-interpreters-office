<?php
/** module/Notes/src/Module.php */

namespace InterpretersOffice\Admin\Rotation;

use Zend\EventManager\EventInterface;
use Zend\Mvc\MvcEvent;
use Zend\View\ViewModel;
use Zend\Session\Container;
use function \date;
/**
 * Module class for our InterpretersOffice\Admin\Rotation module.
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

    /*
     * ViewModel
     *
     * @var ViewModel
     */
    //private $viewModel;

    /**
     * onBootstrap listener
     *
     * @param  EventInterface $event
     * @return void
     */
    public function onBootstrap(EventInterface $event)
    {
        // $log = $event->getApplication()->getServiceManager()->get('log');
        // $log->debug("welcome to ".__NAMESPACE__);
        // $config = $event->getApplication()->getServiceManager()->get('config');
        // $debug = print_r($config['rotations'],true);
        // $log->debug($debug);

    }

}
