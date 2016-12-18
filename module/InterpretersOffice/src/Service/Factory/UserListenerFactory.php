<?php
/**
 * module/InterpretersOffice/src/Service/Factory/UserListenerFactory.php.
 */

namespace InterpretersOffice\Service\Factory;

/** module/InterpretersOffice/src/Factory/UserListenerFactory */

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use InterpretersOffice\Service\Listener\UserListener; 


/**
 * Factory for instantiating user listener service.
 */
class UserListenerFactory implements FactoryInterface {


	/**
     * implements FactoryInterface.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array              $options
     *
     * @return UserListener
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) {

    	return new UserListener(
            $container->get('log'),
            $container->get('entity-manager')
        );

    }

}