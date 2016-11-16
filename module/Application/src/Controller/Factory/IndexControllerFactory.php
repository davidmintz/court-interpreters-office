<?php

/** module/Application/src/Controller/Factory/IndexControllerFactory.php */

namespace Application\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Application\Controller\IndexController;

/**
 * Factory class for instantiating IndexController.
 *
 * To be revised when we determine what its dependences are going to be.
 */
class IndexControllerFactory implements FactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
       // echo "hello... ";
        $obj = $container->get('auth');
        echo get_class($obj). " is our class ... ";
        $return = new IndexController($container->get('annotated-form-factory'),
        		$container->get('entity-manager')
        	);
        $return->auth = $container->get('auth');
        return $return;
    }
}
