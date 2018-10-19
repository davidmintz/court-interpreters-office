<?php
/** module/Requests/src/Controller/Factory/IndexControllerFactory */

namespace InterpretersOffice\Requests\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use InterpretersOffice\Requests\Controller\IndexController;
use InterpretersOffice\Entity\Listener;
use InterpretersOffice\Requests\Acl\ModificationAuthorizedAssertion;
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
        // this is costing 3 more queries even when they are only reading
        // rather than updating, so we need to optimize        
        $user = $entityManager->find('InterpretersOffice\Entity\User',
            $auth->getIdentity()->id);
        $acl->allow($user, $controller, ['update','cancel'],
            new ModificationAuthorizedAssertion($controller));

        return $controller;
    }
}
