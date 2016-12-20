<?php

/** module/InterpretersOffice/src/Controller/Factory/SimpleEntityControllerFactory.php */

namespace InterpretersOffice\Admin\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

use InterpretersOffice\Admin\Controller;

/**
 * Factory for instantiating Controllers for managing our relatively
 * simple entities.
 */
class PeopleControllerFactory implements FactoryInterface
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
        //$array = explode('\\', $requestedName);
        //$baseName = end($array);
        //$shortName = strtolower(substr($baseName, 0, -10));
        return new Controller\PeopleController(
             $container->get('entity-manager')
        );
        /*
        switch ($shortName) {
            
        }*/
    }
}
