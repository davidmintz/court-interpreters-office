<?php

namespace Application\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Authentication\AuthenticationService;
use Application\Service\Authentication\Adapter as AuthenticationAdapter;

class AuthenticationFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        //echo "shit? in ".__CLASS__ . " ... ";
        //return $container->get('doctrine.authenticationservice.orm_default');
        $adapter = new AuthenticationAdapter([
            'object_manager' => $this->em,//'Doctrine\ORM\EntityManager',
            'credential_property' => 'password',
            'credential_callable' => Application\Entity\User::verifyPassword,
            // 'credential_callable' => function (User $user, $passwordGiven) {
            //     return my_awesome_check_test($user->getPassword(), $passwordGiven);
            // },

            ]);
        return new AuthenticationService(null, $adapter);
    }
}
