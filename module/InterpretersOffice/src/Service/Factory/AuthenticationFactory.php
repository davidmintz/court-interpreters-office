<?php
/**
 * module/InterpretersOffice/src/Service/Factory/AuthenticationFactory.php.
 */

namespace InterpretersOffice\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Authentication\AuthenticationService;
use InterpretersOffice\Service\Authentication\Adapter as AuthenticationAdapter;

/**
 * Factory for instantiating authentication service.
 */
class AuthenticationFactory implements FactoryInterface
{
    /**
     * implements FactoryInterface.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array              $options
     *
     * @return AuthenticationService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        
        // try something new, and simpler
        $entityManager = $container->get('entity-manager');
        return new AuthenticationService(null,new AuthenticationAdapter($entityManager));
        
        /* status quo ante, to be cleaned out:
        $options = $container->get('config')['doctrine']['authentication']['orm_default']; 
         // if we don't do the following line, it blows up from trying 
         // to call a method on a string. not sure why.
         $options['object_manager'] = $container->get('entity-manager');
         $storage = $container->get('doctrine.authenticationstorage.orm_default');
         $adapter = new AuthenticationAdapter($options);
         return new AuthenticationService($storage, $adapter);
        */
    }
}
