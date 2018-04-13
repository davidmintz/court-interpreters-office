<?php /** module/Admin/src/Controller/Factory/DefendantsControllerFactory.php */

namespace InterpretersOffice\Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Controller\DefendantsController;
use InterpretersOffice\Entity\Listener;

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
        $em = $container->get('entity-manager');
        //attach the entity listeners
        $resolver = $em->getConfiguration()->getEntityListenerResolver();
        $resolver->register($container->get(Listener\EventEntityListener::class));
        $auth = $container->get('auth');
        $resolver->register($container->get(Listener\UpdateListener::class)->setAuth($auth));
        return new DefendantsController($em);
    }
}
