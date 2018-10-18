<?php
/** module/Requests/src/Controller/Factory/IndexControllerFactory */

namespace InterpretersOffice\Requests\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use InterpretersOffice\Requests\Controller\IndexController;
use InterpretersOffice\Entity\Listener;
use InterpretersOffice\Requests\Acl\OwnershipAssertion;

use InterpretersOffice\Requests\Entity\Listener\RequestEntityListener;

/**
 * Factory class for instantiating Requests\IndexController.
 */
class IndexControllerFactory implements FactoryInterface
{
    /**
     * invocation, if you will.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array              $options
     *
     * @return IndexController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('entity-manager');
        $auth = $container->get('auth');

        $acl = $container->get('acl');
        $controller = new IndexController($entityManager, $auth, $acl);

        $resolver = $entityManager->getConfiguration()->getEntityListenerResolver();
        $resolver->register($container->get(Listener\UpdateListener::class)
            ->setAuth($auth));

        $resolver->register($container->get(RequestEntityListener::class));

        $acl->allow($auth->getIdentity()->role, $controller,
            ['update','cancel'],new OwnershipAssertion(
                $entityManager,$controller
        ));

        return $controller;
    }
}
