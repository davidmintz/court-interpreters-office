<?php
/**  module/Admin/src/Service/Factory/EmailServiceFactory.php */

namespace InterpretersOffice\Admin\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Service\EmailService;

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
        $service
            ->setViewRenderer($container->get('ViewRenderer'))
            ->setAuth($container->get('auth'))
            ->setLogger($log)
            ->setObjectManager($container->get('entity-manager'));

        return $service;
    }
}
