<?php
/** module/InterpretersOffice/src/Controller/Factory/SimpleEntityControllerFactory.php */

namespace InterpretersOffice\Admin\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use InterpretersOffice\Admin\Controller;

use InterpretersOffice\Service\Authentication\AuthenticationAwareInterface;

/**
 * Factory for instantiating Controllers that manage Person, its subclasses, or
 * User entities.
 */
class PeopleControllerFactory implements FactoryInterface
{
    /**
     * instantiates and returns a concrete instance of AbstractActionController.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array              $options
     *
     * @return Zend\Mvc\Controller\AbstractActionController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {

        $em = $container->get('entity-manager');
        if ($requestedName == Controller\InterpretersWriteController::class) {
            // is the Vault thing enabled?
            $vault_enabled = key_exists('vault', $container->get('config'));
            $controller = new $requestedName($em, $vault_enabled);
            // attach InterpreterEntity listener
            $listener = $container->get('interpreter-listener');
            $resolver = $em->getConfiguration()->getEntityListenerResolver();
            $resolver->register($listener);
        } else {
            $controller = new $requestedName($em);
        }
        if ($controller instanceof AuthenticationAwareInterface) {
            $controller->setAuthenticationService($container->get('auth'));
        }
        return $controller;
    }
}
