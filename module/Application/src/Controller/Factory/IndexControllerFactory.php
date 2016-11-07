<?php
/** module/Application/src/Controller/Factory/IndexControllerFactory.php */

namespace Application\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Interop\Container\ContainerInterface;
use Application\Controller\IndexController;

/**
 * Factory class for instantiating IndexController.
 * 
 * To be revised when we determine what its dependences are going to be.
 * 
 */
class IndexControllerFactory implements FactoryInterface {
    
    /** 
     * {@inheritdoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, Array $options = null) {

        return new IndexController($container);
    }

}
