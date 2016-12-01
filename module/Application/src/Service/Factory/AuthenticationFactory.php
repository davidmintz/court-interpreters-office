<?php

namespace Application\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Authentication\AuthenticationService;
use Application\Service\Authentication\Adapter as AuthenticationAdapter;

class AuthenticationFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $adapter = new AuthenticationAdapter([
            'object_manager' => $container->get('entity-manager'),
            'credential_property' => 'password',
            'credential_callable' => 'Application\Entity\User::verifyPassword',
            ]);
        return new AuthenticationService(null, $adapter);
    }
}
