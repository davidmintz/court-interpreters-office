<?php
/**  module/InterpretersOffice/src/Entity/Listener/Factory/EventEntityListenerFactory.php */

namespace InterpretersOffice\Entity\Listener\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Entity\Listener\EventEntityListener;
use InterpretersOffice\Admin\Service\ScheduleListener;
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

        $sharedEvents = $container->get('SharedEventManager');
        /*
        $shit = function($e) use ($log,$user){
            $message = sprintf('event was %s; triggered by %s; current user is %s',
            $e->getName(),get_class($e->getTarget()),print_r($user,true)
           );
            $log->info($message);
        };
        */
        $sharedEvents->attach(
             EventEntityListener::class,
             '*',
             [$container->get(ScheduleListener::class),'doShit']
        );
        return $listener;
    }
}
