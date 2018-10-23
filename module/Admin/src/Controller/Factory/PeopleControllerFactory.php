<?php
/** module/InterpretersOffice/src/Controller/Factory/SimpleEntityControllerFactory.php */

namespace InterpretersOffice\Admin\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use InterpretersOffice\Admin\Controller;
use InterpretersOffice\Admin\Controller\PeopleController;

use InterpretersOffice\Service\Authentication\AuthenticationAwareInterface;
use InterpretersOffice\Entity\Listener;
use SDNY\Vault\Service\Vault;

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
            $vault_enabled = $container->has(Vault::class);
            $controller = new $requestedName($em, $vault_enabled);
            // attach InterpreterEntity listener
            $listener = $container->get('interpreter-listener');
            $resolver = $em->getConfiguration()->getEntityListenerResolver();
            //attach the entity listeners
            $resolver->register($listener);
        } else {
            $controller = new $requestedName($em);
        }
        if ($controller instanceof AuthenticationAwareInterface) {
            $controller->setAuthenticationService($container->get('auth'));
        }
        // ensure UpdateListener knows who current user is
        $container->get(Listener\UpdateListener::class)->setAuth($container->get('auth'));
        
        return $controller;
    }
}
