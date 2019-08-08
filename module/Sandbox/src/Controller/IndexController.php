<?php
namespace Sandbox\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use InterpretersOffice\Requests\Entity\Request;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $serviceManager = $this->getEvent()->getApplication()->getServiceManager();
        $em = $serviceManager->get('entity-manager');
        //$event = $em->find('InterpretersOffice\Requests\Entity\RepositoryEvent',115656);
        $repo = $em->getRepository('InterpretersOffice\Requests\Entity\Request');
        $request = $repo->getRequest(20572);

        // looks like trying to update a read-only entity merely fails
        // without error
        // $hat = $em->getRepository('InterpretersOffice\Entity\Hat')->find(1);
        // $hat->setName("some other shit");
        // $em->flush();

        return (new ViewModel(['request'=>$request]))->setTemplate('sandbox/index/index');
        //$event = $em->getRepository('InterpretersOffice\Entity\Event')->load(115656);
        //$event->getJudge()->getLastname();
        //return new ViewModel(['event'=>$event]);
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
