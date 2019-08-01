<?php
namespace Sandbox\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use InterpretersOffice\Requests\Entity\Request;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $em = $this->getEvent()->getApplication()->getServiceManager()->get('entity-manager');
        //$event = $em->find('InterpretersOffice\Requests\Entity\RepositoryEvent',115656);
        $repo = $em->getRepository('InterpretersOffice\Requests\Entity\Request');
        //$request = $repo->getRequestWithEvent(20572);
        $result = $repo->list_v2();
        $serviceManager = $this->getEvent()->getApplication()->getServiceManager();
        $auth = $serviceManager->get('auth');
        $user = $auth->getIdentity();
        //$entity = $em->find('InterpretersOffice\Entity\User',$user->id);
        $entity = $em->getRepository('InterpretersOffice\Entity\User')->getUser($user->id);
        echo $entity->getRole();
        return (new ViewModel())->setTemplate('sandbox/index/index');
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
