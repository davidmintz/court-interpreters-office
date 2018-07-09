<?php

/** module/InterpretersOffice/src/Controller/Factory/AccountControllerFactory.php */

namespace InterpretersOffice\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use InterpretersOffice\Controller\AccountController;

use InterpretersOffice\Service\AccountManager;

/**
 * Factory class for instantiating AccountController.
 */
class AccountControllerFactory implements FactoryInterface
{
    /**
     * invocation, if you will.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array              $options
     *
     * @return AccountController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = new AccountController(
            $container->get('entity-manager'),
            $container->get('auth')
        );
        return $controller
            ->setAccountManager($container->get(AccountManager::class));

        /** @var $sharedEvents Zend\EventManager\SharedEventManagerInterface */
        // $sharedEvents = $container->get('SharedEventManager');
        // $accountManager = $container->get(AccountManager::class);
        // $log = $container->get('log');
        // $sharedEvents->attach($requestedName,
        //     AccountManager::EVENT_REGISTRATION_SUBMITTED,
        //     [$accountManager,'onRegistrationSubmitted']
        // );

    }
}
