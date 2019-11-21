<?php

namespace InterpretersOffice\Admin\Rotation\Controller;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Rotation\Controller\IndexController;
use InterpretersOffice\Admin\Rotation\Service\TaskRotationService;

class IndexControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return IndexController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new IndexController($container->get(TaskRotationService::class));
    }
}
