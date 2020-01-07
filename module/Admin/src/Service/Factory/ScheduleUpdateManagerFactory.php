<?php
/**  module/Admin/src/Service/Factory/ScheduleUpdateManagerFactory.php */

namespace InterpretersOffice\Admin\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
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
        $object = new ScheduleUpdateManager(
            $container->get('auth'),
            $container->get('config'),
            $container->get('log')
        );

        return $object->setViewRenderer($container->get('ViewRenderer'));
    }
}
