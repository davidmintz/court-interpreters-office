<?php
/**
 * module/Requests/src/Controller/IndexController.php
 */

namespace InterpretersOffice\Requests\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Authentication\AuthenticationServiceInterface;
use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Requests\Entity;
use InterpretersOffice\Entity\CourtClosing;

use InterpretersOffice\Requests\Form;

/**
 *  IndexController for Requests module
 *
 */
class IndexController extends AbstractActionController
{
    /**
     * objectManager instance.
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * authentication service
     *
     * @var AuthenticationServiceInterface;
     */
    protected $auth;

    /** \Zend\Session\Container */
    protected $session;

    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param AuthenticationServiceInterface $auth
     */
    public function __construct(ObjectManager $objectManager, AuthenticationServiceInterface $auth)
    {
        $this->objectManager = $objectManager;
        $this->auth = $auth;
        $this->session = new \Zend\Session\Container("requests");
    }

    public function viewAction()
    {

    }
    public function searchAction()
    {

    }
    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        return new ViewModel();
    }

    public function testAction()
    {
        $view = new ViewModel();
        $view->setTemplate('interpreters-office/requests/index/index.phtml');

        return $view;
    }

    /**
     * displays the user's requests
     *
     * @return ViewModel
     */
    public function listAction()
    {
        $repo = $this->objectManager->getRepository(Entity\Request::class);
        $page = $this->params()->fromQuery('page');
        if ($page) {
            $this->session->list_page = $page;
        } else {
            $page = $this->session->list_page ?: 1;
        }
        $paginator = $repo->list(
            $this->auth->getIdentity(),$page
        );
        if ($paginator) {
            $ids = array_column($paginator->getCurrentItems()->getArrayCopy(),'id');
            $defendants = $repo->getDefendants($ids);
        } else {
            $defendants = [];
        }
        $deadline = $this->getTwoBusinessDaysFromDate();
        $view = new ViewModel(compact('paginator','defendants','deadline'));
        $view->setTerminal($this->getRequest()->isXmlHttpRequest());

        return $view;
    }

    /* under consideration
    public function deadlineAction()
    {
        return new JsonModel(['deadline'=>$this->getTwoBusinessDaysFromDate()]);
    }
    */

    /**
     * gets datetime two business days from $date
     *
     * @param  \DateTime $date
     * @return string
     */
    protected function getTwoBusinessDaysFromDate(\DateTime $date = null)
    {
        return $this->objectManager
            ->getRepository(CourtClosing::class)
            ->getTwoBusinessDaysFromDate($date)->format('Y-m-d H:i:s');
    }

    /**
     * creates a new Request
     *
     * @return JsonModel
     */
    public function createAction()
    {
        $view = new ViewModel();
        $view->setTemplate('interpreters-office/requests/index/form.phtml');
        $form = new Form\RequestForm($this->objectManager,
            ['action'=>'create','auth'=>$this->auth]);
        $view->form = $form;
        $entity = new Entity\Request();
        $form->bind($entity);
        if ($this->getRequest()->isPost()) {
            try {
                $form->setData($this->getRequest()->getPost());
                if (! $form->isValid()) {
                    return new JsonModel(['validation_errors' =>
                        $form->getMessages()]);
                }
                // post-validation: make sure it is not a near-exact duplicate
                $repo = $this->objectManager->getRepository(Entity\Request::class);
                if ($repo->findDuplicate($entity)) {
                    return  new JsonModel(
                    ['validation_errors'=> ['request' =>  ['duplicate'=>
                        [
                        'there is already a request with this date, time,
                        judge, type of event, defendant(s), docket, and language'
                    ]]]]);
                }
                $form->postValidate();
                $this->objectManager->persist($entity);
                $this->objectManager->flush();
                $this->flashMessenger()->addSuccessMessage(
                'Your request for interpreting services has been submitted. Thank you.'
                );
                return  new JsonModel(['status'=> 'success','id'=>$entity->getId()]);

            } catch (\Exception $e) { //throw $e;
                $this->getResponse()->setStatusCode(500);
                return new JsonModel(['message'=>$e->getMessage(),]);
            }
        }

        return $view;
    }

    /**
     * updates a Request
     *
     * @return JsonModel
     */
    public function updateAction()
    {
        $view = new ViewModel();
        $view->setTemplate('interpreters-office/requests/index/form.phtml');
        $id = $this->params()->fromRoute('id');
        $form = new Form\RequestForm($this->objectManager,
            ['action'=>'update','auth'=>$this->auth]);

        $view->setVariables(['form' => $form, 'id' => $id]);

        return $view;
    }
}
