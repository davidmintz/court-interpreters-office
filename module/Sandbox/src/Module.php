<?php namespace Sandbox;
use Zend\Mvc\MvcEvent;
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
        $eventManager = $event->getApplication()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_ROUTE,function($e){
            $routeMatch = $e->getRouteMatch();
            if (! $routeMatch) {
                return;
            }
            $module = $routeMatch->getParams()['module'];
            if ('Sandbox' == $module ) {
                $viewModel = $e->getApplication()->getMvcEvent()
                        ->getViewModel();
                $viewModel->setTemplate('sandbox/layout');
            }
        });
    }
}
