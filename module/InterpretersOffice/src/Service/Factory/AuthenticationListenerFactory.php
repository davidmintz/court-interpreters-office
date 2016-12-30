<?php
/**
 * module/InterpretersOffice/src/Service/Factory/UserListenerFactory.php.
 */

namespace InterpretersOffice\Service\Factory;

/** module/InterpretersOffice/src/Factory/UserListenerFactory */

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use InterpretersOffice\Service\Listener\AuthenticationListener;

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
     * @return UserListener
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {

        return new AuthenticationListener(
            $container->get('log'),
            $container->get('entity-manager')
        );
    }
}
