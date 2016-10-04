<?php

namespace Application\Controller\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Interop\Container\ContainerInterface;
use Application\Controller\IndexController;

class IndexControllerFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
	{
		return new IndexController($serviceLocator->getServiceLocator());
	}

	public function __invoke(ContainerInterface $container, $requestedName, Array $options = null) {
		$serviceLocator = $container->getServiceLocator();
		return new IndexController($serviceLocator);
	}
}