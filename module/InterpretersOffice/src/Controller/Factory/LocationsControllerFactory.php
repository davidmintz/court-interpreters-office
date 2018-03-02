<?php
/**  module/InterpretersOffice/src/Controller/Factory/LocationsControllerFactory.php */

namespace InterpretersOffice\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Controller\LocationsController;

/**
 * factory for LocationsController
 */
class LocationsControllerFactory implements FactoryInterface
{
    /**
     * implements FactoryInterface
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return LocationsController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new LocationsController($container->get(\Doctrine\ORM\EntityManager::class));
    }
}
