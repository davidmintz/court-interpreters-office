<?php
/**
 * module/InterpretersOffice/src/Module.php.
 */

namespace InterpretersOffice;

use InterpretersOffice\View\Helper;
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

    public function getViewHelperConfig()
    {
        return [
            
            'aliases' => [
                'defendantName' =>  Helper\DefendantName::class,
            ],
            'factories' => [
                Helper\DefendantName::class => function($container){
                    $manager = $container->get('ViewHelperManager');
                    return new Helper\DefendantName($manager->get("escapeHtml"));
                }
            ],
        ];
        
    }
    /*
     * module bootstrap, opportunity to attach listeners etc.
     *
     * @param \Zend\Mvc\MvcEvent $e The MvcEvent instance

    public function onBootstrap(\Zend\Mvc\MvcEvent $e)
    {
       
       //$container = $e->getApplication()->getServiceManager();
       //$shit = $container->get('ViewHelperManager');
       //var_dump (get_class ($shit->get('defendantName')) );
       //$app->getEventManager()->attach('render', [$this, 'testSomething'], 100);
    }
    //*/

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
