<?php

namespace Application\Service\Factory;

use Interop\Container\ContainerInterface;

use Zend\ServiceManager\Factory\FactoryInterface;

class AuthenticationFactory implements FactoryInterface
{

	public function __invoke(ContainerInterface $container, $requestedName, Array $options = null)
    {
    	return $container->get('doctrine.authenticationservice.orm_default');
    }


}