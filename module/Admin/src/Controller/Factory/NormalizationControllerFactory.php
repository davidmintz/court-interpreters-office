<?php

namespace InterpretersOffice\Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Controller\SearchController;

class SearchControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return NormalizationController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SearchController($container->get('entity-manager'));
    }
}
