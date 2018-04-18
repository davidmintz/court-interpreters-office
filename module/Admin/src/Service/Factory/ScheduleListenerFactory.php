<?php
/**  module/Admin/src/Service/Factory/ScheduleListenerFactory.php */

namespace InterpretersOffice\Admin\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Service\ScheduleListener;

/**
 * ScheduleListenerFactory
 */
class ScheduleListenerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return ScheduleListener
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ScheduleListener($container->get('log'),$container->get('auth'));
    }
}
