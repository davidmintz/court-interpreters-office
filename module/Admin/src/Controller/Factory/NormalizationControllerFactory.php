<?php

namespace InterpretersOffice\Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Controller\NormalizationController;

class NormalizationControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return NormalizationController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new NormalizationController($container->get('entity-manager'));
    }
}
