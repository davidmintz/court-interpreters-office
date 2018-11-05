<?php
/** module/Requests/src/Controller/Factory/IndexControllerFactory */

namespace InterpretersOffice\Requests\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use InterpretersOffice\Requests\Controller\IndexController;
use InterpretersOffice\Requests\Controller\UpdateController;
use InterpretersOffice\Entity\Listener;
use InterpretersOffice\Requests\Acl\ModificationAuthorizedAssertion;
use InterpretersOffice\Requests\Entity\Listener\RequestEntityListener;

use InterpretersOffice\Requests\Controller\Admin;

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
        $resolver = $entityManager->getConfiguration()->getEntityListenerResolver();
        $resolver->register($container->get(Listener\UpdateListener::class)
            ->setAuth($auth));
        // admin controller
        if ($requestedName == Admin\IndexController::class) {
            return new Admin\IndexController($entityManager,$auth);
        }
        // submitters' controller
        $acl = $container->get('acl');
        $controller = new $requestedName($entityManager, $auth, $acl);


        $resolver->register($container->get(RequestEntityListener::class));
        // HELLO! this is us costing 3 queries even when they are only reading
        // rather than updating, so we need to optimize: split off into two
        // controllers
        if ($requestedName == UpdateController::class) {
            $user = $entityManager->find('InterpretersOffice\Entity\User',
            $auth->getIdentity()->id);
            $acl->allow($user, $controller, ['update','cancel'],
            new ModificationAuthorizedAssertion($controller));
        }

        return $controller;
    }
}
