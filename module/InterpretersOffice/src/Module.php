<?php
/**
 * module/InterpretersOffice/src/Module.php.
 */

namespace InterpretersOffice;

use InterpretersOffice\Form\View\Helper;

use InterpretersOffice\Admin\Form\View\Helper as ViewHelper;

use Laminas\ServiceManager\Factory\InvokableFactory;

/**
 * Module class for application's main module.
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

    /*
     * DOES NOT SEEM TO WORK
     * https://docs.zendframework.com/zend-view/quick-start/#creating-and-registering-alternate-rendering-and-response-strategies.
     *
     * @param \Laminas\Mvc\MvcEvent $e The MvcEvent instance

    public function registerJsonStrategy($e)
    {
        $app = $e->getTarget();
        $locator = $app->getServiceManager();
        $view = $locator->get('Laminas\View\View');
        $jsonStrategy = $locator->get('ViewJsonStrategy');

        // Attach strategy, which is a listener aggregate, at high priority
        $view->getEventManager()->attach(
            \Laminas\View\ViewEvent::EVENT_RENDERER,
            [$jsonStrategy, 'selectRenderer'],
            100
        );
    }

     */
}
