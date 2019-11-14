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
        $container =  $event->getApplication()->getMvcEvent()->getApplication()
            ->getServiceManager();
        $auth = $container->get('auth');
        if ($auth->hasIdentity() && $auth->getIdentity()->role != 'submitter') {
            $viewModel = $event->getApplication()->getMvcEvent()
                ->getViewModel();
            $viewModel->notes_enabled = true; // by default
            $this->viewModel = $viewModel;
            $eventManager = $event->getApplication()->getEventManager();
            $eventManager->attach(MvcEvent::EVENT_RENDER,[$this,'initialize'],10);
        }

    }

    public function initialize(EventInterface $event)
    {
        $container =  $event->getApplication()->getMvcEvent()->getApplication()
            ->getServiceManager();
        $log = $container->get('log');
        $log->debug("here's Johnny! ".__METHOD__);
    }
}
