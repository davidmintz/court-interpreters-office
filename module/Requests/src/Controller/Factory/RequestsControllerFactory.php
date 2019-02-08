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
 * Factory class for instantiating Requests\IndexController.
 *
 * This factory produces the read-only IndexController as well as the
 * WriteController for the Request module. If the $requestedName (controller)
 * we're creating is the WriteController, we update the ACL (which is mostly
 * configured by now) on the fly, so that we can add an ACL assertion that
 * depends on conditions we can't know until this controller is created, and
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
        /** */
        $entityManager = $container->get('entity-manager');
        $auth = $container->get('auth');
        $resolver = $entityManager->getConfiguration()
            ->getEntityListenerResolver();

        if ($requestedName == Controller\WriteController::class) {
            //$sql_logger = new \InterpretersOffice\Service\SqlLogger($container->get('log'));
            //$entityManager->getConfiguration()->setSQLLogger($sql_logger);
            // add Doctine entity listeners
            $container->get('log')->debug("HELLO? Factory is creating $requestedName");
            $resolver->register($container->get(Listener\UpdateListener::class)
                ->setAuth($auth));
            $resolver->register($container->get(RequestEntityListener::class));
            $resolver->register($container->get(EventEntityListener::class));

            // add another ACL rule
            $acl = $container->get('acl');
            $controller = new $requestedName($entityManager, $auth, $acl);
            $user = $entityManager->find(
                'InterpretersOffice\Entity\User',
                $auth->getIdentity()->id
            );
            $acl->allow(
                $user,
                $controller,
                ['update','cancel'],
                new ModificationAuthorizedAssertion($controller)
            );

            // experimental. let the general entity UpdateListener trigger events,
            // and this listener will call the ScheduleUpdateManager
            // $eventManager = $container->get('SharedEventManager');
            //
            // $eventManager->attach(Listener\UpdateListener::class, '*',
            // function ($e) use ($container) {
            //     $container->get('log')->debug(
            //         "SHIT HAS BEEN TRIGGERED! {$e->getName()} is the event, calling ScheduleUpdateManager"
            //     );
            //     /** @var ScheduleUpdateManager $updateManager */
            //     $updateManager = $container->get(ScheduleUpdateManager::class);
            //     $updateManager->onUpdateRequest($e);
            // });

            return $controller;
        }

        if ($requestedName == AdminController::class) {
            $container->get('log')->debug(
                "attaching entity listeners in RequestsControllerFactory (AdminController)..."
            );
            $listener = $container->get(EventEntityListener::class);
            $listener->setAuth($container->get('auth'));
            $resolver->register($container->get(EventEntityListener::class));
            $resolver->register($container->get(RequestEntityListener::class));
        }
        return new $requestedName($entityManager, $auth);
    }
}
