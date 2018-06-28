<?php

/** module/InterpretersOffice/src/Controller/Factory/AccountControllerFactory.php */

namespace InterpretersOffice\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\EventManager\SharedEventManagerInterface;
use Interop\Container\ContainerInterface;
use InterpretersOffice\Controller\AccountController;

use InterpretersOffice\Service\AccountManager;

/**
 * Factory class for instantiating IndexController.
 *
 * To be revised when we determine what its dependences are going to be.
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
     * @return ExampleController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = new AccountController(
            $container->get('entity-manager'),
            $container->get('auth')
        );
        /** @var $sharedEvents Zend\EventManager\SharedEventManagerInterface */
        $sharedEvents = $container->get('SharedEventManager');
        $accountManager = $container->get(AccountManager::class);
        $log = $container->get('log');
        $sharedEvents->attach($requestedName, AccountManager::REGISTRATION_SUBMITTED,
            [$accountManager,'onRegistrationSubmitted']
        );
        //$pluginManager = $container->get('ControllerPluginManager');
        //$pluginManager->get('layout')->setTemplate('...');
        return $controller;
    }
}
