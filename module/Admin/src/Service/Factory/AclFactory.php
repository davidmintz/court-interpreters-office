<?php
/**
 * module/Admin/src/Service/Factory/AclFactory.php.
 */

namespace InterpretersOffice\Admin\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Service\Acl;
use Zend\EventManager\EventManager;

/**
 * Factory for instantiating ACL service.
 */
class AclFactory implements FactoryInterface
{
    /**
     * implements FactoryInterface.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array              $options
     *
     * @return Acl
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config')['acl'];
        $log = $container->get('log');
        $auth = $container->get('auth');
        $acl = new Acl($config);
        $sharedEventManager = $container->get('SharedEventManager');
        $sharedEventManager->attach(
            get_class($acl),
            'access-denied',
            function ($e) use ($log, $auth) {
                $message = sprintf(
                    "access DENIED to user %s in role %s: resource %s, action %s",
                    $auth->getIdentity()->email,
                    $e->getParam('role'),
                    $e->getParam('resource'),
                    $e->getParam('privilege', 'N/A')
                );
                 $log->warn($message);
            }
        );
       // note to self: if $acl implements EventManagerAwareInterface,
       // it seems we don't have to do the following ourselves
       // $acl->setEventManager(new EventManager($sharedEventManager));
        return $acl;
    }
}
