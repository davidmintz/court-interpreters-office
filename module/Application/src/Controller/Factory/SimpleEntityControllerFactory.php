<?php

/** module/Application/src/Controller/Factory/SimpleEntityControllerFactory.php */

namespace Application\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

/**
 * Factory for instantiating Controllers for managing our relatively
 * simple entities.
 */
class SimpleEntityControllerFactory implements FactoryInterface
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
        $array = explode('\\', $requestedName);
        $baseName = end($array);
        $shortName = strtolower(substr($baseName, 0, -10));

        switch ($shortName) {
            case 'languages':
            case 'locations':
                $pluginManager = $container->get('FormElementManager');
                $entityManager = $container->get('entity-manager');

                return new $requestedName($entityManager, $pluginManager, $shortName);
            break;
            // to be continued
        }
    }
}
