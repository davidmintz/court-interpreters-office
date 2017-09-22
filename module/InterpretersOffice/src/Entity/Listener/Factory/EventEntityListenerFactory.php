<?php

namespace InterpretersOffice\Entity\Listener\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Entity\Listener\EventEntityListener;

class EventEntityListenerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return EventEntityListener
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $auth = $container->get('auth');
        $log  = $container->get('log'); // maybe get rid of this at some point
        $listener = new EventEntityListener();
        $listener->setLogger($container->get('log'));
        /** @todo see what happens if we make this a constructor dependency */
        $listener->setAuth($container->get('auth'));
        return $listener;
        
    }
}
