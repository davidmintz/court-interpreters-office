<?php

namespace InterpretersOffice\Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Controller\DefendantsController;

class DefendantsControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return DefendantsController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new DefendantsController($container->get('entity-manager'));
    }
}
