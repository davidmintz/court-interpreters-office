<?php /** module/Admin/src/Controller/Factory/NormalizationControllerFactory.php */

namespace InterpretersOffice\Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Controller\NormalizationController;

/**
 * factory for NormalizationController
 */
class NormalizationControllerFactory implements FactoryInterface
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return NormalizationController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new NormalizationController($container->get('entity-manager'));
    }
}
