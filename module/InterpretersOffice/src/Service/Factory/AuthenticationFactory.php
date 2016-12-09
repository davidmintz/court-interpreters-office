<?php
/**
 * module/InterpretersOffice/src/Service/Factory/AuthenticationFactory.php
 */
namespace InterpretersOffice\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Authentication\AuthenticationService;
use InterpretersOffice\Service\Authentication\Adapter as AuthenticationAdapter;

/**
 * Factory for instantiating authentication service
 */
class AuthenticationFactory implements FactoryInterface
{
    
    /**
     * implements FactoryInterface
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return \InterpretersOffice\Form\Factory\AnnotatedEntityFormFactory
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $adapter = new AuthenticationAdapter([
            'object_manager' => $container->get('entity-manager'),
            'credential_property' => 'password',
            'credential_callable' => 'InterpretersOffice\Entity\User::verifyPassword',
            ]);
        return new AuthenticationService(null, $adapter);
    }
}
