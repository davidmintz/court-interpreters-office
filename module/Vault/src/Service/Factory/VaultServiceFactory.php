<?php
/**
 * module/Vault/src/Service/Factory/VaultServiceFactory.php
 */

namespace SDNY\Vault\Service\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

use SDNY\Vault\Service\Vault;

/**
 * VaultServiceFactory
 *
 * @author david@davidmintz.org
 */
class VaultServiceFactory implements FactoryInterface{
  
    /**
     * invocation, so to speak
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array              $options
     *
     * @return Vault
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $service = new Vault(
            $container->get('config')['vault']
        );
        
        return $service;
    }

}
