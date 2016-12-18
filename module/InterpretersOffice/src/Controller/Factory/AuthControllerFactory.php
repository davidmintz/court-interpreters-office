<?php

/** module/InterpretersOffice/src/Controller/Factory/AuthControllerFactory.php */

namespace InterpretersOffice\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Zend\Authentication\AuthenticationService;

use InterpretersOffice\Service\Authentication\Adapter as AuthAdapter;
use InterpretersOffice\Controller\AuthController;
use InterpretersOffice\Service\Listener\UserListener;

/**
 * Factory for instantiating AuthController.
 */
class AuthControllerFactory
{
    /**
      * implements FactoryInterface.
      *
      * @param ContainerInterface $container
      * @param string $requestedName
      * @param array $options
      *
      * @return AuthController
      */
     public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
     {
         $adapter = new AuthAdapter([
            'object_manager' => $container->get('entity-manager'),
             // we could hard-code this into our adapter
            'credential_callable' => 'InterpretersOffice\Entity\User::verifyPassword',
         ]);
         
        $service = new AuthenticationService(null, $adapter);

        // attach event listeners

        $sharedEvents = $container->get("SharedEventManager");
        $listener = $container->get(UserListener::class);        
        $sharedEvents->attach($requestedName,'loginAction',
           [ $listener, 'onLogin']
        );
        /*
        $sharedEvents->attach($requestedName,'authentication_failure',
           [ $listener, 'onAuthenticationFailure']
        );
        */
        return new AuthController($service);
     }
}
