<?php

/** module/Admin/src/Controller/IndexController */

namespace InterpretersOffice\Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

/**
 * controller for admin/index.
 */
class IndexController extends AbstractActionController
{
    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {        
        // debug
        $container = $this->getEvent()->getApplication()->getServiceManager();
        // $em = $container->get('entity-manager');
        // $cache = $em->getConfiguration()->getResultCacheImpl();
        // echo get_class($cache);
        $acl_config = $container->get('config')['acl'];
        $module_manager = $container->get("ModuleManager");
        $modules = array_keys($module_manager->getLoadedModules());
       
        return new ViewModel([
            'modules' => $modules,
            'acl_config' => $acl_config, 'acl' => $container->get("acl")
        ]);
    
    }

    /**
     * serves the support page (WIP)
     */
    public function supportAction()
    {
    }
}
