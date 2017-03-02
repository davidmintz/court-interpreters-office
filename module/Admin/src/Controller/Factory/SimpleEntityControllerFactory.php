<?php

/** module/InterpretersOffice/src/Controller/Factory/SimpleEntityControllerFactory.php */

namespace InterpretersOffice\Admin\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

use InterpretersOffice\Entity\Listener;
use Doctrine\ORM\Events;

use Zend\Filter\Word\CamelCaseToDash as Filter;

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
        $basename = substr($requestedName, strrpos($requestedName,'\\')+1);
        $shortName = strtolower((new Filter)->filter(substr($basename, 0, -10)));
        // $shortName is for identifying cache id and maybe composing path 
        // to a form.phtml viewscript 
        /**
         * @todo rethink this whole plan
         */
        switch ($shortName) {
            
            case 'languages':
            case 'locations':
            case 'event-types':

                $factory = $container->get('annotated-form-factory');               
                $entityManager = $container->get('entity-manager');
                ///*
                $entityManager->getEventManager()
                    ->addEventListener([Events::postPersist,Events::postUpdate //Events::postRemove,
                        ],
                     // constructor argument to be changed
                     new Listener\UpdateListener($shortName,$container->get('log'))
               );
               $controller = new $requestedName($entityManager, $factory, $shortName); 
            break;            
        }
        return $controller;
    }
}
