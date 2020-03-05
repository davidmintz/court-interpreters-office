<?php
/** docket-annotations-controller factory */
namespace InterpretersOffice\Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Controller\DocketAnnotationsController;
use InterpretersOffice\Entity\Listener;
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
     * @return DocketAnnotationsController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $em = $container->get('entity-manager');
        $auth = $container->get('auth');
        $service = new DocketAnnotationService($em, $auth);
        //attach the entity listeners
        $resolver = $em->getConfiguration()->getEntityListenerResolver();
        $resolver->register($container->get(Listener\UpdateListener::class)->setAuth($auth));

        return new $requestedName($service);
    }
}
