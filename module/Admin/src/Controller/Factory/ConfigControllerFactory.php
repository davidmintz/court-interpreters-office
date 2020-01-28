<?php
/** module/Admin/src/Controller/Factory/ConfigControllerFactory.php */
namespace InterpretersOffice\Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Controller\ConfigController;

class ConfigControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return ConfigController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        // does the current user have write access?
        $acl = $container->get('acl');
        $auth = $container->get('auth');
        $role = $auth->getStorage()->read()->role;
        $write_access = $acl->isAllowed($role, ConfigController::class, 'post');
        
        return new ConfigController(['write_access'=>$write_access]);
    }
}
