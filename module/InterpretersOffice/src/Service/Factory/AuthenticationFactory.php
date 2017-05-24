<?php
/**
 * module/InterpretersOffice/src/Service/Factory/AuthenticationFactory.php.
 */

namespace InterpretersOffice\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Authentication\AuthenticationService;
use InterpretersOffice\Service\Authentication\Adapter as AuthenticationAdapter;

/**
 * Factory for instantiating authentication service.
 */
class AuthenticationFactory implements FactoryInterface
{
    /**
     * implements FactoryInterface.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array              $options
     *
     * @return AuthenticationService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $adapter = new AuthenticationAdapter([
            'object_manager' => $container->get('entity-manager'),
            'credential_property' => 'password',
            'credential_callable' => 'InterpretersOffice\Entity\User::verifyPassword',
            ]);
        $storage = $container->get('doctrine.authenticationstorage.orm_default');
        echo "\nfactory is instantiating an AuthenticationService...\n";    
        return new AuthenticationService($storage, $adapter);
    }
}
