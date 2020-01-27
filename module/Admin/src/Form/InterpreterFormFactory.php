<?php
/**
 * module/Admin/src/Form/InterpreterFormFactory.php */

namespace InterpretersOffice\Admin\Form;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use function is_readable;
use function file_get_contents;
use function json_decode;

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
        $options = ['action' => $action, 'vault_enabled' =>  $vault_config['enabled']];
        $form_config_file = 'module/Admin/config/forms.json';
        if (is_readable($form_config_file)) {
            $form_config = json_decode(file_get_contents($form_config_file),\JSON_OBJECT_AS_ARRAY)['interpreters'];
            $options = array_merge($options,$form_config);
        }
        $container->get('log')->debug(print_r($options,true));
        return new InterpreterForm($container->get('entity-manager'), $options);
    }
}
