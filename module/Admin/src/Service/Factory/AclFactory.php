<?php
/**
 * module/Admin/src/Service/Factory/AclFactory.php.
 */

namespace InterpretersOffice\Admin\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use InterpretersOffice\Admin\Service\Acl;

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
       return new Acl($config);
    }
}
