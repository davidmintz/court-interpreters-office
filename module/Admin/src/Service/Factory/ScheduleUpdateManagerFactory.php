<?php
/**  module/Admin/src/Service/Factory/ScheduleUpdateManagerFactory.php */

namespace InterpretersOffice\Admin\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Service\ScheduleUpdateManager;

/**
 * ScheduleUpdateManagerFactory
 */
class ScheduleUpdateManagerFactory implements FactoryInterface
{
    /**
     * implements FactoryInterface.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return ScheduleUpdateManager
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ScheduleUpdateManager(
            $container->get('auth'),
            $container->get('log'),
            $container->get('config')
        );
    }
}
