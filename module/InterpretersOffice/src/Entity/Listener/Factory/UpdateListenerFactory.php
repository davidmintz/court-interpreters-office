<?php
/**
 * module/InterpretersOffice/src/Entity/Listener/Factory/UpdateListenerFactory.php
 */

namespace InterpretersOffice\Entity\Listener\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use InterpretersOffice\Entity\Listener\UpdateListener;

/**
 * Factory for instantiating Entity\UpdateListener
 */
class UpdateListenerFactory implements FactoryInterface
{

    /**
     * time to get invoked!
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array              $options
     *
     * @return UpdateListener
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $listener = new UpdateListener();
        $listener->setLogger($container->get('log'));

        // echo "Not daring to pull auth from container<br>";
        // because it causes a infinite loop/fatal error --
        // functions nested > 256 levels. sounds like bullshit to me!
        // or a cyclic dependency thing.
        // $auth = $container->get(\Laminas\Authentication\AuthenticationService::class);

        return $listener;
    }
}
