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
        $acl_config = $container->get('config')['acl'];
        // $module_manager = $container->get("ModuleManager");

        return new ViewModel(['acl_config'=>$acl_config, 'acl'=>$container->get("acl")]);
    }
}
