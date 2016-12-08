<?php
namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
//use Interop\Container\ContainerInterface;

class IndexController extends AbstractActionController {
    
    public function indexAction()
    {
        //echo "HELLO!<br>";
       //return false;
       return new ViewModel();   
    }
    
}
