<?php

namespace Application\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Authentication\AuthenticationService;


class AuthenticationFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        //echo "shit? in ".__CLASS__ . " ... ";
        //return $container->get('doctrine.authenticationservice.orm_default');
        return new AuthenticationService;
    }
}
