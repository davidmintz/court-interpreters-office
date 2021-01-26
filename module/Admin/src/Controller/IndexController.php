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
     * configuration
     * 
     * @var array $config
     */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        
    }
    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {                
        return new ViewModel($this->config);     
    }

    /**
     * serves the support page (WIP)
     */
    public function supportAction()
    {        
        return new ViewModel($this->config); 
    }
}
