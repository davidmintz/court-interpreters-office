<?php
/**  module/Admin/src/Service/Factory/EmailServiceFactory.php */

namespace InterpretersOffice\Admin\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Service\EmailService;
use InterpretersOffice\Admin\Service\Log\Writer as DbWriter;
/**
 * EmailServiceFactory
 */
class EmailServiceFactory implements FactoryInterface
{
    /**
     * implements FactoryInterface.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return EmailService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $service = new EmailService($container->get('config'));
        $log = $container->get('log');
        if (! $log->getWriterPluginManager()->has(DbWriter::class)) {
            $log->addWriter($container->get(DbWriter::class), 100);// [, $priority, $options]
        }
        $service
            ->setViewRenderer($container->get('ViewRenderer'))
            ->setAuth($container->get('auth'))
            ->setLogger($log)
            ->setEventManager($container->get('SharedEventManager'));

        return $service;
    }
}
