<?php

/** module/Application/src/Controller/Factory/AuthControllerFactory.php */

namespace Application\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

use Zend\Authentication\AuthenticationService;
use Application\Service\Authentication\Adapter as AuthAdapter;
use Application\Controller\AuthController;

/**
 * Factory for instantiating AuthController
 * 
 * 
 */
class AuthControllerFactory {
    
    /**
     * {@inheritdoc}
     */
     public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
     {
         $adapter = new AuthAdapter([
            'object_manager' => $container->get('entity-manager'),
            'credential_property' => 'password',
         ]);
         $service = new AuthenticationService(null, $adapter);
         return new AuthController($service);
     }
    
}
