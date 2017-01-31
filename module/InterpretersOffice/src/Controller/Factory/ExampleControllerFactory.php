<?php

/** module/InterpretersOffice/src/Controller/Factory/ExampleControllerFactory.php */

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
     * @return ExampleController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = new ExampleController(
            $container->get('entity-manager')
        );

        return $controller;
    }
}
