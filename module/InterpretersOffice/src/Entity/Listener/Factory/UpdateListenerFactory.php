<?php
/**
 * module/InterpretersOffice/src/Entity/Listener/Factory/UpdateListenerFactory.php
 */

namespace InterpretersOffice\Entity\Listener\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
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

        return $listener;
    }
}
