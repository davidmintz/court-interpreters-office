<?php

namespace InterpretersOffice\Admin\Rotation\Controller;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Rotation\Controller\IndexController;
use InterpretersOffice\Admin\Rotation\Service\TaskRotationService;
use InterpretersOffice\Entity\Listener\UpdateListener;
class Factory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return IndexController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $listener = $container->get(UpdateListener::class);
        $listener->setAuth($container->get('auth'));

        return new $requestedName($container->get(TaskRotationService::class));
    }
}
