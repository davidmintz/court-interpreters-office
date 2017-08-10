<?php
/** module/InterpretersOffice/src/Controller/Factory/EventsControllerFactory.php */

namespace InterpretersOffice\Admin\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use InterpretersOffice\Admin\Controller\EventsController;

use InterpretersOffice\Service\Authentication\AuthenticationAwareInterface;

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
        $controller = new EventsController(
             $container->get('entity-manager'),
             $container->get('auth')
        );
        // more initialization?

        return $controller;
    }
}
