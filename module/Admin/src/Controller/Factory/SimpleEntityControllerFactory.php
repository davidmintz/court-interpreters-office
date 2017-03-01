<?php

/** module/InterpretersOffice/src/Controller/Factory/SimpleEntityControllerFactory.php */

namespace InterpretersOffice\Admin\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

use InterpretersOffice\Entity\Listener;
use Doctrine\ORM\Events;
//use Interpreters\Admin\Controller\LanguagesController;

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
        // might be useful for identifying cache namespace and 
        // path to a form.phtml viewscript file
        $shortName = strtolower(substr($baseName, 0, -10));
        //substr($requestedName, strrpos($requestedName,'\\')+1)
        
        switch ($shortName) {
            case 'languages':
            case 'locations':
            case 'eventtypes':
                //echo "WTF ... $shortName ";
                $factory = $container->get('annotated-form-factory');               
                $entityManager = $container->get('entity-manager');
                $entityManager->getEventManager()
                    ->addEventListener([Events::postUpdate,Events::postRemove,Events::postPersist],
                     // constructor argument to be changed
                     new Listener\UpdateListener($container->get('log'))
               );
                //echo "returning a $requestedName instance... ";
                return new $requestedName($entityManager, $factory, $shortName); 
            break;
            // to be continued
        }
    }
}
