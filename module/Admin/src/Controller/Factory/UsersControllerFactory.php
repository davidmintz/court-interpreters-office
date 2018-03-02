<?php /** module/Admin/src/Controller/Factory/UsersControllerFactory.php */

namespace InterpretersOffice\Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Controller\UsersController;

/**
 * UsersController factory
 */
class UsersControllerFactory implements FactoryInterface
{
    /**
     * implements FactoryInterface
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return UsersController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new UsersController(
                $container->get('entity-manager'),
                $container->get('auth')
        );
        //$controller->setAuthenticationService
        //return $controller;
    }
}
