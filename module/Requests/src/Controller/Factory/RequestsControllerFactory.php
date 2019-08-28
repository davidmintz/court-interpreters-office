<?php
/** module/Requests/src/Controller/Factory/IndexControllerFactory */

namespace InterpretersOffice\Requests\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use InterpretersOffice\Requests\Controller;
use InterpretersOffice\Entity\Listener;
use InterpretersOffice\Entity\Listener\EventEntityListener;
use InterpretersOffice\Requests\Acl\ModificationAuthorizedAssertion;
use InterpretersOffice\Requests\Entity\Listener\RequestEntityListener;
use InterpretersOffice\Service\SqlLogger;

use InterpretersOffice\Requests\Controller\Admin\IndexController as AdminController;

use InterpretersOffice\Admin\Service\ScheduleUpdateManager;

/**
 * Factory class for instantiating controllers in Requests module.
 *
 * This factory produces the read-only IndexController as well as the
 * WriteController for the Requests module, and the Admin\IndexController.
 * If the $requestedName (controller) we're creating is the either IndexController or
 * WriteController, we update the ACL (which is mostly configured by now) on the
 * fly, so that we can add an ACL assertion that depends on conditions we can't
 * know until we have Request and User entities in hand, i.e., once this
 * controller is created. And the overhead seems enough to warrant waiting until
 * we know we need it.
 */
class RequestsControllerFactory implements FactoryInterface
{
    /**
     * invocation, if you please.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array              $options
     *
     * @return \Zend\Mvc\Controller\AbstractActionController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        // @var \Zend\Log\Logger $log */
        //$log = $container->get('log');

        $entityManager = $container->get('entity-manager');
        $auth = $container->get('auth');
        $acl = $container->get('acl');
        $resolver = $entityManager->getConfiguration()
            ->getEntityListenerResolver();
        $resolver->register($container->get(Listener\UpdateListener::class)
                ->setAuth($auth));
        $controller = new $requestedName($entityManager, $auth, $acl);
        // crude, but...
        if (method_exists($controller,'setUserEntity')) {
            //... both the IndexController and WriteController in the
            //  InterpretersOffice\Requests\Controller namespace need to
            //  be able to check ACL
            $user = $entityManager->getRepository('InterpretersOffice\Entity\User')
                ->getUser($auth->getIdentity()->id);
            $controller->setUserEntity($user);
            $acl->allow(
                $user,
                $controller,
                ['update','cancel'],
                new ModificationAuthorizedAssertion($controller)
            );
        }
        if ($requestedName == Controller\IndexController::class) {
            return $controller;
        }
        if ($requestedName == Controller\WriteController::class) {

            //$sql_logger = new \InterpretersOffice\Service\SqlLogger($container->get('log'));
            //$entityManager->getConfiguration()->setSQLLogger($sql_logger);

            // add Doctrine entity listeners
            $resolver->register($container->get(RequestEntityListener::class));
            $container->get('log')->debug("attached our RequestEntityListener in RequestsControllerFactory");
            $resolver->register($container->get(EventEntityListener::class));

            // attach event listeners
            $eventManager = $container->get('SharedEventManager');
            $scheduleManager = $container->get(ScheduleUpdateManager::class);
            $eventManager->attach(
                $requestedName,
                'updateRequest',
                [$scheduleManager,'onUpdateRequest']
            );
            $eventManager->attach(
                $requestedName,
                'cancel',
                [$scheduleManager,'onCancelRequest']
            );
            $eventManager->attach(
                RequestEntityListener::class,
                'create',
                [$scheduleManager,'onCreateRequest']
            );

            return $controller;
        }

        if ($requestedName == AdminController::class) {
            // $container->get('log')->debug(
            //     "attaching entity listeners in RequestsControllerFactory (AdminController)..."
            // );
            $resolver->register($container->get(RequestEntityListener::class));
            $listener = $container->get(EventEntityListener::class);
            $listener->setAuth($container->get('auth'));
            $resolver->register($listener);
        }

        return new $requestedName($entityManager, $auth);
    }
}
