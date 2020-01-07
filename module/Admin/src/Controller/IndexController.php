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

        return new ViewModel(['title' => 'admin']);
    }
}
