<?php
/**
 * module/InterpretersOffice/src/Entity/Listener/Factory/InterpreterEntityListenerFactory.php
 */

namespace InterpretersOffice\Entity\Listener\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

use InterpretersOffice\Entity\Listener\InterpreterEntityListener;
use SDNY\Vault\Service\Vault;

/**
 * InterpreterEntityListenerFactory
 *
 */
class InterpreterEntityListenerFactory implements FactoryInterface
{

    /**
     * instantiates Interpreter entity listener
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array              $options
     *
     * @return InterpreterEntityListener
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        // more to come?
        //$sharedEventManager = $container->get('SharedEventManager');
        //$sharedEventManager->attach($requestedName,'*',function($e){echo $e->getName() . " happened... ";});
        $listener = new InterpreterEntityListener();
        $listener->setLogger($container->get('log'));
        if ($container->has(Vault::class)) {
            $listener->setVaultService($container->get(Vault::class));
        }
        return $listener;
    }
}
