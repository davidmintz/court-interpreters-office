<?php
/** docket-annotations-controller factory */
namespace InterpretersOffice\Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Controller\DocketAnnotationsController;

use InterpretersOffice\Admin\Service\DocketAnnotationService;

/** factory */
class DocketAnnotationsControllerFactory implements FactoryInterface
{
    /**
     * invokes
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return SearchController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $service = new DocketAnnotationService($container->get('entity-manager'));
        return new DocketAnnotationsController($service);
    }
}
