<?php
/**  module/InterpretersOffice/src/Entity/Listener/Factory/EventEntityListenerFactory.php */

namespace InterpretersOffice\Entity\Listener\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Entity\Listener\EventEntityListener;
use InterpretersOffice\Admin\Service\ScheduleListener;

/**
 * Factory for the Event entity listener.
 *
 * This instantiates the Doctrine entity listener for the Event entity, and also
 * attaches the ScheduleListener to the ZF event system so that we can listen to
 * certain changes in Event entities in a central location.
 *
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
        $listener->setAuth($container->get('auth'));
        $sharedEvents = $container->get('SharedEventManager');
        $sharedEvents->attach(
            EventEntityListener::class,
            '*',
            [$container->get(ScheduleListener::class),'scheduleChange']
        );
        return $listener;
    }
}
