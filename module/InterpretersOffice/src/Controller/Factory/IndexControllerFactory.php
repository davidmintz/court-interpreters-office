<?php
/** module/InterpretersOffice/src/Controller/Factory/IndexControllerFactory.php */

namespace InterpretersOffice\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Controller\IndexController;


/**
 * Factory for IndexController
 */
class IndexControllerFactory implements FactoryInterface
{
    /**
     * yadda yadda
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return IndexController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new IndexController($container->get('entity-manager'));
    }
}
