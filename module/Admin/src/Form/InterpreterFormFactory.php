<?php
/**
 * module/Admin/src/Form/InterpreterFormFactory.php */

namespace InterpretersOffice\Admin\Form;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Factory for instantiating ACL service.
 */
class InterpreterFormFactory implements FactoryInterface
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

        $uri = $container->get('Request')->getUri();
        $action = strstr($uri,'/edit/') ? "update":"create";
        // is the Vault thing enabled?
        $config = $container->get('config');
        $vault_config = $config['vault'] ?? [ 'enabled' => false ];
        return new InterpreterForm(
            $container->get('entity-manager'),
            ['action' => $action, 'vault_enabled' =>  $vault_config['enabled']]
        );
    }
}
