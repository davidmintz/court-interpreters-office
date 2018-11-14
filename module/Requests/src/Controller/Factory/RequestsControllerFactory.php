<?php
/** module/Requests/src/Controller/Factory/IndexControllerFactory */

namespace InterpretersOffice\Requests\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use InterpretersOffice\Requests\Controller;
use InterpretersOffice\Entity\Listener;
use InterpretersOffice\Requests\Acl\ModificationAuthorizedAssertion;
use InterpretersOffice\Requests\Entity\Listener\RequestEntityListener;

/**
 * Factory class for instantiating Requests\IndexController.
 *
 * This factory produces the read-only IndexController as well as the
 * WriteController for the Request module. If the $requestedName (controller)
 * we're creating is the WriteController, we update the ACL (which is mostly
 * configured by now) on the fly, so that we can add an ACL assertion that
 * depends on conditions we can't know until this controller is initialized, and
 * which is also expensive enough to warrant delaying until we know we
 * need it.
 */
class RequestsControllerFactory implements FactoryInterface
{
    /**
     * invocation, if you will.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array              $options
     *
     * @return \Zend\Mvc\Controller\AbstractActionController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('entity-manager');
        $auth = $container->get('auth');
        // add Doctine entity listeners
        $resolver = $entityManager->getConfiguration()
            ->getEntityListenerResolver();
        $resolver->register($container->get(Listener\UpdateListener::class)
            ->setAuth($auth));
        $resolver->register($container->get(RequestEntityListener::class));
        if ($requestedName == Controller\WriteController::class) {
            $acl = $container->get('acl');
            $controller = new $requestedName($entityManager, $auth, $acl);
            $user = $entityManager->find('InterpretersOffice\Entity\User',
            $auth->getIdentity()->id);
            $acl->allow($user, $controller, ['update','cancel'],
                new ModificationAuthorizedAssertion($controller));

            return $controller;
        }

        return new $requestedName($entityManager, $auth);
    }
}
