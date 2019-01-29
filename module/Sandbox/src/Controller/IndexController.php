<?php
namespace Sandbox\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $em = $this->getEvent()->getApplication()->getServiceManager()->get('entity-manager');
        $event = $em->find('InterpretersOffice\Entity\Event',115656);
        //$event = $em->getRepository('InterpretersOffice\Entity\Event')->load(115656);
        //$event->getJudge()->getLastname();
        return new ViewModel(['event'=>$event]);
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
