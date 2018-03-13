<?php
/** module/InterpretersOffice/src/Controller/Factory/EventsControllerFactory.php */

namespace InterpretersOffice\Admin\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use InterpretersOffice\Admin\Controller\EventsController;

use InterpretersOffice\Entity\Listener;


/**
 * Factory for instantiating EventController
 */
class EventsControllerFactory implements FactoryInterface
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
        $auth = $container->get('auth');
        $em = $container->get('entity-manager');
        $controller = new EventsController(
            $em,
            $auth // maybe we won't need this
        );
        //attach the entity listeners
        $resolver = $em->getConfiguration()->getEntityListenerResolver();
        $resolver->register($container->get(Listener\EventEntityListener::class));
        $resolver->register($container->get(Listener\UpdateListener::class));

        return $controller;
    }
}
