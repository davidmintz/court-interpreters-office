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
        if ($requestedName == Controller\InterpretersController::class) {
            // attach Entity Listener
            $listener = $container->get('interpreter-listener');
            $em->getConfiguration()->getEntityListenerResolver()->register($listener);
        }
        $controller = new $requestedName($em);
        if ($controller instanceof AuthenticationAwareInterface) {
            $controller->setAuthenticationService($container->get('auth'));
        }
        return $controller;
    }
}
