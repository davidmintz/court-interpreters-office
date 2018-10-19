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
use Zend\Permissions\Acl\Resource\ResourceInterface;
use InterpretersOffice\Admin\Service\Acl;
use InterpretersOffice\Requests\Form;

use Zend\Mvc\MvcEvent;

/**
 *  IndexController for Requests module
 *
 */
class IndexController extends AbstractActionController implements ResourceInterface
{
    /**
     * objectManager instance.
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
    * Request entity.
    *
    * @var Entity\Request
    */
    protected $entity;

    /**
     * authentication service
     *
     * @var AuthenticationServiceInterface;
     */
    protected $auth;

    /**
     * Acl - access control service
     *
     * @var Acl
     */
    protected $acl;

    /**
     * session
     *
     * @var \Zend\Session\Container
     */
    protected $session;

    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param AuthenticationServiceInterface $auth
     */
    public function __construct(ObjectManager $objectManager,
        AuthenticationServiceInterface $auth, Acl $acl )
    {
        $this->objectManager = $objectManager;
        $this->auth = $auth;
        $this->acl = $acl;
        $this->session = new \Zend\Session\Container("requests");
    }

    /**
     * implements ResourceInterface
     *
     * @return string
     */
    public function getResourceId()
    {
         return self::class;
    }


    /**
     * gets the Request entity we're working with
     *
     * @return Entity\Request
     */
    public function getEntity()
    {
        return $this->entity;
    }

    public function getIdentity()
    {
        return $this->auth->getIdentity();
    }
    /**
     * onDispatch event Listener
     *
     * @param  MvcEvent $e
     * @return mixed
     */
    public function onDispatch($e)
    {
        $params = $this->params()->fromRoute();

        if (in_array($params['action'],['update','cancel'])) {
            $entity = $this->objectManager->find(Entity\Request::class,
                $params['id']);
            if (! $entity) {
                return parent::onDispatch($e);
            }
            $this->entity = $entity;
            $user = $this->objectManager->find('InterpretersOffice\Entity\User',
                $this->auth->getIdentity()->id);
            $allowed = $this->acl->isAllowed(
                 $user, $this, $params['action']
            );
            if (! $allowed) {
                $viewRenderer = $e->getApplication()->getServiceManager()
                    ->get('ViewRenderer');
                $url  = $this->getPluginManager()->get('url')
                    ->fromRoute('requests/view',['id'=>$entity->getId()]);
                $viewModel = $e->getViewModel();
                $viewModel->setVariables(
                    ['content'=>$viewRenderer->render(
                    (new ViewModel())
                    ->setTemplate('interpreters-office/requests/denied')
                    ->setVariables([
                        'message' =>
                        "Sorry, you are not authorized to {$params['action']}
                        this <a href=\"$url\">request</a>."])
                    )]
                );

                return $this->getResponse()
                    ->setContent($viewRenderer->render($viewModel));
            }
        }

        return parent::onDispatch($e);
    }

    public function viewAction()
    {
        $id = $this->params()->fromRoute('id');
        $repository = $this->objectManager->getRepository(Entity\Request::class);
        return [
            'data'=>$repository->view($id),
            'deadline'=>  $this->getTwoBusinessDaysFromDate()
        ];

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
        $defendants = [];
        $paginator = $repo->list($this->auth->getIdentity(),$page);
        if ($paginator) {
            $data = $paginator->getCurrentItems()->getArrayCopy();
            // wish we were kidding, but...
            $ids = array_column(array_column($data,0),'id');
            $defendants = $repo->getDefendants($ids);
        }
        $deadline = $this->getTwoBusinessDaysFromDate();
        //echo $deadline->format("Y-m-d");
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
    public function getTwoBusinessDaysFromDate(\DateTime $date = null)
    {
        return $this->objectManager
            ->getRepository(CourtClosing::class)
            ->getTwoBusinessDaysFromDate($date);
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
        if (! $this->getRequest()->isPost()) {
            $repeat_id = $this->params()->fromRoute('id');
            if ($repeat_id) {
                $repo = $this->objectManager
                    ->getRepository(Entity\Request::class)
                    ->populate($entity,$repeat_id);
            }
        }
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

            } catch (\Exception $e) {
                $this->getResponse()->setStatusCode(500);
                $this->events->trigger('error',$this,['exception'=>$e,
                    'details'=>'doing create in Requests module']);
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

        $id = $this->params()->fromRoute('id');
        $entity = $this->objectManager->find(Entity\Request::class,$id);
        if (! $entity) {
            $this->flashMessenger()->addErrorMessage(
                "The request with id $id was not found in the database");
            return $this->redirect()->toRoute('requests');
        }
        $form = new Form\RequestForm($this->objectManager,
            ['action'=>'update','auth'=>$this->auth]);
        $form->bind($entity);

        if (! $this->getRequest()->isPost()) {
            $view = new ViewModel();
            $view->setTemplate('interpreters-office/requests/index/form.phtml')
                ->setVariables(['form' => $form, 'id' => $id]);
            return $view;
        }
        $form->setData($this->getRequest()->getPost());
        if (! $form->isValid()) {
            return new JsonModel(['validation_errors'=>$form->getMessages()]);
        }
        try {
            $this->objectManager->flush();
            $this->flashMessenger()->addSuccessMessage(
            'This request for interpreting services has been updated successfully. Thank you.'
            );
            return new JsonModel(['status'=>'success']);
            //return (new ViewModel(['form' => $form, 'id' => $id]))
            //->setTemplate('interpreters-office/requests/index/form.phtml');
        } catch (\Exception $e) {
            $this->getResponse()->setStatusCode(500);
            $this->events->trigger('error',$this,['exception'=>$e,
                'details'=>'doing update in Requests module']);
            return new JsonModel(['message'=>$e->getMessage(),]);
        }
    }
}

/*

// DEBUG
$log = $this->getEvent()->getApplication()
    ->getServiceManager()->get('log');
$previous_datetime = [

    'date'=>$entity->getDate()->format("YYYY-MM-DD"),
    'time' => $entity->getTime()->format("H:i"),
];

$new_datetime = [
    'date'=>$entity->getDate()->format("YYYY-MM-DD"),
    'time' => $entity->getTime()->format("H:i"),
];
foreach (['date','time'] as $field) {
    if ($previous_datetime[$field] == $new_datetime[$field]) {
        $log->debug("looks like unchanged $field");
    }
}
*/
