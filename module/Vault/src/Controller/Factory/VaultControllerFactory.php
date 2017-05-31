<?php
/**
 * module/Vault/src/Controller/Factory/VaultControllerFactory.php
 */

namespace SDNY\Vault\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

use SDNY\Vault\Controller\VaultController;
use SDNY\Vault\Service\Vault as VaultService;

/**
 * VaultControllerFactory
 *
 * @author david@davidmintz.org
 */
class VaultControllerFactory implements FactoryInterface {
  
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
        return new VaultController($container->get(VaultService::class));
    }

}
