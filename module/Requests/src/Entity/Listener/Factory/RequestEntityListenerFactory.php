<?php
/** module/Requests/src/Entity/Listener/Factory/RequestEntityListenerFactory.php  */

namespace InterpretersOffice\Requests\Entity\Listener\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Requests\Entity\Listener\RequestEntityListener;

/**
 * factory class for the Request entity listener
 */
class RequestEntityListenerFactory implements FactoryInterface
{
    /**
     * implements FactoryInterface
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return RequestEntityListener
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {

        $listener = new RequestEntityListener();
        $listener->setLogger($container->get('log'));
        $listener->setAuth($container->get('auth'));
        //$sharedEvents = $container->get('SharedEventManager');
        //$sharedEvents->attach(/*... */);

        return $listener;
    }
}
