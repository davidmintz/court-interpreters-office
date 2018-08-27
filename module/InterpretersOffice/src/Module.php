<?php
/**
 * module/InterpretersOffice/src/Module.php.
 */

namespace InterpretersOffice;

use InterpretersOffice\Form\View\Helper;

use InterpretersOffice\Admin\Form\View\Helper as ViewHelper;

use Zend\ServiceManager\Factory\InvokableFactory;

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

    /**
     * gets viewhelper config
     *
     * @return array
     */
    public function getViewHelperConfig()
    {
        return [

            'aliases' => [
                'defendant' => ViewHelper\DefendantElementCollection::class,
            ],
            'factories' => [
                ViewHelper\Defendant::class => function ($container) {
                    $manager = $container->get('ViewHelperManager');
                    //$manager->get("escapeHtml")
                    return new ViewHelper\DefendantElementCollection();
                }
            ],
        ];
    }
    

    /*
     * DOES NOT SEEM TO WORK
     * https://docs.zendframework.com/zend-view/quick-start/#creating-and-registering-alternate-rendering-and-response-strategies.
     *
     * @param \Zend\Mvc\MvcEvent $e The MvcEvent instance

    public function registerJsonStrategy($e)
    {
        $app = $e->getTarget();
        $locator = $app->getServiceManager();
        $view = $locator->get('Zend\View\View');
        $jsonStrategy = $locator->get('ViewJsonStrategy');

        // Attach strategy, which is a listener aggregate, at high priority
        $view->getEventManager()->attach(
            \Zend\View\ViewEvent::EVENT_RENDERER,
            [$jsonStrategy, 'selectRenderer'],
            100
        );
    }

     */
}
