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

    /**
     * module bootstrap, opportunity to attach listeners etc
     * 
     * @param  \Zend\Mvc\MvcEvent $e The MvcEvent instance
     * @return void
     */
    public function onBootstrap($e)
    {
       // from the ZF3 tutorial, a waste of time:
       
        // Register a "render" event, at high priority (so it executes prior
       // to the view attempting to render)
       // $app = $e->getApplication();
       // $app->getEventManager()->attach('render', [$this, 'registerJsonStrategy'], 100);
    }

    /**
     * DOES NOT WORK!
     * https://docs.zendframework.com/zend-view/quick-start/#creating-and-registering-alternate-rendering-and-response-strategies
     * @param  \Zend\Mvc\MvcEvent $e The MvcEvent instance
     * @return void
     */
    public function registerJsonStrategy($e)
    {
        $app          = $e->getTarget();
        $locator      = $app->getServiceManager();
        $view         = $locator->get('Zend\View\View');
        $jsonStrategy = $locator->get('ViewJsonStrategy');

        // Attach strategy, which is a listener aggregate, at high priority
        $view->getEventManager()->attach(
            \Zend\View\ViewEvent::EVENT_RENDERER,
            [$jsonStrategy,'selectRenderer'], 100);
    }
}

