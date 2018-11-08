<?php
namespace Sandbox\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $em = $this->getEvent()->getApplication()->getServiceManager()->get('entity-manager');
        $em->find('InterpretersOffice\Entity\Event',115656);
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
