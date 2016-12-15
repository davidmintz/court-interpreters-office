<?php

/** module/InterpretersOffice/src/Controller/Factory/IndexControllerFactory.php */

namespace InterpretersOffice\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use InterpretersOffice\Controller\ExampleController;

/**
 * Factory class for instantiating IndexController.
 *
 * To be revised when we determine what its dependences are going to be.
 */
class ExampleControllerFactory implements FactoryInterface
{
    /**
     * invocation, if you will.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array              $options
     *
     * @return IndexController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = new ExampleController();
        $sharedEvents = $container->get("SharedEventManager");
        //$sharedEvents->attach("*","doShit",function($event){
        //    echo "woo hoo ! I am the callback set by the factory.<br>";
        //});    
        $sharedEvents->attach(
            $requestedName,

            "doShit",function($event) use ($requestedName){
            echo "woo hoo ! I am the event handler attached by the factory for target $requestedName.<br>";
            echo "event is a ".get_class($event);
        });


        return $controller;
    }
}
