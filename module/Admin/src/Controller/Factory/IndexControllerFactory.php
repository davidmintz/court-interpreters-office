<?php

namespace InterpretersOffice\Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Controller\IndexController;
use InterpretersOffice\Requests\Entity\Request;

class IndexControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return IndexController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        
        $module_manager = $container->get("ModuleManager");
        $modules = array_keys($module_manager->getLoadedModules());
        $config = [
            'acl'=>$container->get('acl'),
            'modules' => $modules,
            'app_config' => $container->get('config'),
        ];
        if (in_array('InterpretersOffice\Requests',$modules)) {
            /** @var InterpretersOffice\Requests\Entity\RequestRepository $repo  */
            $repo = $container->get('entity-manager')->getRepository(Request::class);
            $config['requests_pending'] = $repo->countPending();
        }

        return new IndexController($config);
    }
}
