<?php
/** module/Application/src/Controller/Factory/SimpleEntityControllerFactory.php */

namespace Application\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Interop\Container\ContainerInterface;
use Application\Controller\IndexController;

/**
 * Factory for instantiating Controllers for managinag our relatively 
 * simple entities
 * 
 */
class SimpleEntityControllerFactory implements FactoryInterface {
    
    /** 
     * {@inheritdoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, Array $options = null) {
        
        $array = explode('\\',$requestedName);
        $baseName = end($array);
        $what = strtolower(substr($baseName,0,-10));
        
        switch ($what) {
            case 'languages':
            case 'locations':
                return new $requestedName($container);
            break;
            // to be continued
        }
        
        
    }

}
