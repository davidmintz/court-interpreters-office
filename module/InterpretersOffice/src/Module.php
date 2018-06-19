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
                'defendantName' => ViewHelper\DefendantNameElementCollection::class,
            ],
            'factories' => [
                ViewHelper\DefendantName::class => function ($container) {
                    $manager = $container->get('ViewHelperManager');
                    //$manager->get("escapeHtml")
                    return new ViewHelper\DefendantNameElementCollection();
                }
            ],
        ];
    }
    /**
     * module bootstrap, opportunity to attach listeners etc.
     *
     * @param \Zend\Mvc\MvcEvent $e The MvcEvent instance
     */
    public function onBootstrap(\Zend\Mvc\MvcEvent $e)
    {
        return;

       /** @todo remove this. nice try but this is not the way to go */
        $container = $e->getApplication()->getServiceManager();
        $entityManager = $container->get('entity-manager');
        $eventManager = $entityManager->getEventManager();
        $listeners = array_values($eventManager->getListeners('postLoad'));
        $listener = null;
        foreach ($listeners as $object) {
            //echo get_class($object);
            if ($object instanceof \InterpretersOffice\Entity\Listener\UpdateListener) {
                $listener = $object;
                break;
            }
        }
        if ($listener) {
            $listener->setAuthenticationService($container->get('auth'));
            $container->get('log')
                   ->debug('auth service injected into UpdateListener in Admin Bootstrap');
        }
    }
    //

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
