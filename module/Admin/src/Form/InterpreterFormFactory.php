<?php
/**
 * module/Admin/src/Form/InterpreterFormFactory.php */

declare(strict_types=1);

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
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : InterpreterForm
    {

        $uri = $container->get('Request')->getUri()->toString();
        // printf('<pre>%s</pre>',print_r(get_class_methods($uri),true));
        $action = strstr($uri,'/edit/') ? "update":"create";
        // is the Vault thing enabled?
        $config = $container->get('config');
        $vault_config = $config['vault'] ?? [ 'enabled' => false ];
        $options = ['action' => $action, 'vault_enabled' =>  $vault_config['enabled'],'constrain_email'=>false];
        $form_config_file = 'module/Admin/config/forms.json';
        if (is_readable($form_config_file)) {
            $form_config = json_decode(file_get_contents($form_config_file),true)['interpreters'];
            $options = array_merge($options,$form_config);
        }
        
        return new InterpreterForm($container->get('entity-manager'), $options);
    }
}
