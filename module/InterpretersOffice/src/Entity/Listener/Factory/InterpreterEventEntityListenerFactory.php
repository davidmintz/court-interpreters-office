<?php

namespace InterpretersOffice\Entity\Listener\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Entity\Listener\InterpreterEventEntityListener;

class InterpreterEventEntityListenerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return InterpreterEventEntityListener
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $listener = new InterpreterEventEntityListener();
        $listener->setAuth($container->get('auth'));
        return $listener;
    }
}
