<?php

/** module/InterpretersOffice/src/Controller/Factory/AuthControllerFactory.php */

namespace InterpretersOffice\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

use Zend\Authentication\AuthenticationService;
use InterpretersOffice\Service\Authentication\Adapter as AuthAdapter;
use InterpretersOffice\Controller\AuthController;

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
