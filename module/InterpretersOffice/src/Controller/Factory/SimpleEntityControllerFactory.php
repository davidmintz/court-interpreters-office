<?php

/** module/InterpretersOffice/src/Controller/Factory/SimpleEntityControllerFactory.php */

namespace InterpretersOffice\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

use Interpreters\Admin\Controller\LanguagesController;


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
                
                $factory = $container->get('annotated-form-factory');
                $entityManager = $container->get('entity-manager');
                
                return new $requestedName($entityManager, $factory);//, $shortName
            break;
            // to be continued
        }
    }
}
