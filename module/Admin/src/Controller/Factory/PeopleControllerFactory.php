<?php
/** module/InterpretersOffice/src/Controller/Factory/SimpleEntityControllerFactory.php */

namespace InterpretersOffice\Admin\Controller\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use InterpretersOffice\Admin\Controller;
use InterpretersOffice\Admin\Controller\PeopleController;

use InterpretersOffice\Service\Authentication\AuthenticationAwareInterface;
use InterpretersOffice\Entity\Listener;
use SDNY\Vault\Service\Vault;

use InterpretersOffice\Admin\Form;

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
     * @return Laminas\Mvc\Controller\AbstractActionController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {

        $em = $container->get('entity-manager');
        if ($requestedName == Controller\InterpretersWriteController::class) {
            // is the Vault thing enabled?
            $config = $container->get('config');
            $vault_config = isset($config['vault']) ? $config['vault'] : ['enabled' => false ];
            $vault_enabled = $vault_config['enabled'];
            $form = $container->get(Form\InterpreterForm::class);
            $controller = new $requestedName($em, $form, $vault_enabled);

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
