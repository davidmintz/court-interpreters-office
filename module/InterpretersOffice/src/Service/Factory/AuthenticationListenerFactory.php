<?php
/**
 * module/InterpretersOffice/src/Service/Factory/UserListenerFactory.php.
 */

namespace InterpretersOffice\Service\Factory;

/* module/InterpretersOffice/src/Factory/UserListenerFactory */

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Service\Listener\AuthenticationListener;
use InterpretersOffice\Admin\Service\Log\Writer as DbWriter;
/**
 * Factory for instantiating user listener service.
 */
class AuthenticationListenerFactory implements FactoryInterface
{
    /**
     * implements FactoryInterface.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array              $options
     *
     * @return AuthenticationListener
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $log = $container->get('log');
        if (!$log->getWriterPluginManager()->has(DbWriter::class)) {
            $log->addWriter($container->get(DbWriter::class),100);// [, $priority, $options])
        }
        return new AuthenticationListener(
            $log,
            $container->get('entity-manager')
        );
    }
}
