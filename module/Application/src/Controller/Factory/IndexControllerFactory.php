<?php
/** module/Application/src/Controller/Factory/IndexControllerFactory.php */
namespace Application\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Interop\Container\ContainerInterface;
use Application\Controller\IndexController;

/**
 * Factory class for instanating IndexController.
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
