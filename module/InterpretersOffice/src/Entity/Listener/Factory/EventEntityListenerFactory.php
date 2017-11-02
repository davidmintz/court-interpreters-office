<?php
/**  module/InterpretersOffice/src/Entity/Listener/Factory/EventEntityListenerFactory.php */

namespace InterpretersOffice\Entity\Listener\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Entity\Listener\EventEntityListener;

/**
 * factory class for the Event entity listener
 */
class EventEntityListenerFactory implements FactoryInterface
{
    /**
     * implements FactoryInterface
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return EventEntityListener
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        
        $listener = new EventEntityListener();
        $listener->setLogger($container->get('log'));
        /** @todo see what happens if we make this a constructor dependency */
        $listener->setAuth($container->get('auth'));
        // echo "is this an issue in ".__METHOD__. "?  ....... ";
        return $listener;
        
    }
}
