<?php /** module/Admin/src/Controller/Factory/DefendantsControllerFactory.php */

namespace InterpretersOffice\Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Controller\DefendantsController;

/**
 * DefendantsControllerFactory
 */
class DefendantsControllerFactory implements FactoryInterface
{
    /**
     * invocation
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return DefendantsController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new DefendantsController($container->get('entity-manager'));
    }
}
