<?php
/**  module/InterpretersOffice/src/Entity/Listener/Factory/EventEntityListenerFactory.php */

namespace InterpretersOffice\Entity\Listener\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Entity\Listener\EventEntityListener;

//use InterpretersOffice\Admin\Service\ScheduleUpdateManager;

/**
 * Factory for the Event entity listener.
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
        // $sharedEvents = $container->get('SharedEventManager');
        // $sharedEvents->attach(
        //     EventEntityListener::class,
        //     '*',
        //     [$container->get(ScheduleUpdateManager::class),'scheduleChange']
        // );
        return $listener;
    }
}
