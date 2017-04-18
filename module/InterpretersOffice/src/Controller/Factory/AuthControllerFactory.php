<?php

/** module/InterpretersOffice/src/Controller/Factory/AuthControllerFactory.php */

namespace InterpretersOffice\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Zend\Authentication\AuthenticationService;
use InterpretersOffice\Service\Authentication\Adapter as AuthAdapter;
use InterpretersOffice\Controller\AuthController;
use InterpretersOffice\Service\Listener\AuthenticationListener;

/**
 * Factory for instantiating AuthController.
 */
class AuthControllerFactory
{
    /**
     * implements FactoryInterface.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array              $options
     *
     * @todo rethink this approach
     * 
     * @return AuthController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        // this here is not the best design. the interpreter entity listener is
        // intended to be used for encryption/decryption of certain fields and we 
        // are only wiring it in here to keep a listener callback from blowing up
        // by trying to trigger() on its 'events' instance before the container
        // has a chance to automatically setEventManager() on it
        $entityManager =  $container->get('entity-manager');
        $listener = $container->get('interpreter-listener');
        $entityManager->getConfiguration()->getEntityListenerResolver()->register($listener);
        
        $adapter = new AuthAdapter([
          'object_manager' => $entityManager,
           // we should hard-code this into the adapter
          'credential_callable' => 'InterpretersOffice\Entity\User::verifyPassword',
        ]);
        $service = new AuthenticationService(null, $adapter);

        // attach event listeners
        $sharedEvents = $container->get('SharedEventManager');
        $listener = $container->get(AuthenticationListener::class);
        $sharedEvents->attach(
            $requestedName,
            'loginAction',
            [$listener, 'onLogin']
        );
        $sharedEvents->attach(
            $requestedName,
            'logoutAction',
            [$listener, 'onLogout']
        );

        return new AuthController($service);
    }
}
