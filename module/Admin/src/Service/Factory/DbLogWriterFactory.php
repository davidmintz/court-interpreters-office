<?php
/**  module/Admin/src/Service/Factory/DbLogWriterFactory.php */

namespace InterpretersOffice\Admin\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Service\Log\Writer as DbWriter;
use Laminas\Log\Filter\Priority;
use Laminas\Log\Logger;

/**
 * db log-writer factory
 */
class DbLogWriterFactory implements FactoryInterface
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
        $pdo = $container->get('entity-manager')
            ->getConnection()->getWrappedConnection();
        $writer = new DbWriter($pdo);
        $writer->addFilter(new Priority(Logger::INFO));

        return $writer;
    }
}
