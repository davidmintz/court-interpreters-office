<?php

namespace InterpretersOffice\Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Controller\ScheduleController;

class ScheduleControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return ScheduleController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ScheduleController($container->get('entity-manager'));
    }
}
