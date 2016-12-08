<?php
/** module/Admin/src/Controller/IndexController */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * controller for admin/index
 */

class IndexController extends AbstractActionController {
    
    /**
     * index action
     * @return ViewModel
     */
    public function indexAction()
    {
       return new ViewModel();   
    }
    
}
