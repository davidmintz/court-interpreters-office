<?php
/** module/Admin/src/Controller/Factory/ConfigControllerFactory.php */
namespace InterpretersOffice\Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Controller\ConfigController;

class ConfigControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return ConfigController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ConfigController(
            //$container->get('entity-manager')
        );
    }
}
