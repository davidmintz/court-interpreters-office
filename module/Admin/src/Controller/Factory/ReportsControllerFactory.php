<?php
/** reports-controller factory module/Admin/src/Controller/Factory/ReportsControllerFactory.php */
namespace InterpretersOffice\Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Controller\ReportsController;

/** factory */
class ReportsControllerFactory implements FactoryInterface
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return SearchController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ReportsController($container->get('entity-manager'));
    }
}
