<?php
namespace Sandbox\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }

    public function oneAction()
    {
        return (new ViewModel())->setTemplate('sandbox/index/index');
    }

    public function twoAction()
    {
        return (new ViewModel())->setTemplate('sandbox/index/index');
    }

    public function threeAction()
    {
        return (new ViewModel())->setTemplate('sandbox/index/index');
    }
}
