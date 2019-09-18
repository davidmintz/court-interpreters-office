<?php
/**
 * module/Requests/src/Controller/IndexController.php
 */

namespace InterpretersOffice\Requests\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Requests\Entity;
use InterpretersOffice\Entity\CourtClosing;
use InterpretersOffice\Entity\User;
use InterpretersOffice\Entity\Repository\CourtClosingRepository;
use InterpretersOffice\Requests\Form\SearchForm;
use InterpretersOffice\Admin\Service\Acl;
use InterpretersOffice\Service\DateCalculatorTrait;

use Zend\Mvc\MvcEvent;
use Zend\Http\Request;

/**
 *  IndexController for Requests module
 *
 */
class IndexController extends AbstractActionController implements ResourceInterface
{
    use DateCalculatorTrait;
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


    /** @var InterpretersOffice\Entity\User */
    private $user_entity;

    /**
     * session
     *
     * @var \Zend\Session\Container
     */
    protected $session;

    /**
     * Acl - access control service
     *
     * @var Acl
     */
    protected $acl;

    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param AuthenticationServiceInterface $auth
     * @param Acl $acl
     */
    public function __construct(
        ObjectManager $objectManager,
        AuthenticationServiceInterface $auth,
        Acl $acl)
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
     * set currently authorized user entity
     * @param User $user
     */
    public function setUserEntity(User $user)
    {
        $this->user_entity = $user;

        return $this;
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

    /**
     * gets current user
     *
     * @return stdClass
     */
    public function getIdentity()
    {
        return $this->auth->getIdentity();
    }


    /**
     * view Request details
     *
     * @return Array
     */
    public function viewAction()
    {
        $id = $this->params()->fromRoute('id');
        $repository = $this->objectManager->getRepository(Entity\Request::class);
        $this->entity = $repository->getRequest($id);
        $csrf = (new \Zend\Validator\Csrf('csrf'))->getHash();
        if ($this->entity) {
            $write_access = $this->acl->isAllowed(
                $this->user_entity,
                $this,
                'update'
            );
        } else {
            $write_access = null;
        }
        return [
            'data' => $this->entity,
            'deadline' => $this->getTwoBusinessDaysAfterDate(new \DateTime),
            'csrf' => $csrf,'write_access'=>$write_access
        ];
    }

    /**
     * search action
     *
     * @return ViewModel|JsonModel
     *
     */
    public function searchAction()
    {
        $query = $this->params()->fromQuery();
        $form = new SearchForm($this->objectManager,['user'=>$this->getIdentity()]);
        $page = (int)$this->params()->fromQuery('page',1);
        $repository = $this->objectManager->getRepository(Entity\Request::class);
        $deadline = $this->getTwoBusinessDaysAfterDate(new \DateTime);
        $csrf = (new \Zend\Validator\Csrf('csrf'))->getHash();
        if (!$query) {
            if ($this->session->search_defaults) {
                $defaults = $this->session->search_defaults;
                $params = $defaults;
                $form->setData($params);
                $results = $repository->search($params,$defaults['page']);
            } else {
                $results = null;
            }
            return new ViewModel(compact('form','results','deadline','csrf'));
        }
        // else, we have form/query data to validate
        $form->setData($query);
        if (!$form->isValid()) {
            $response = [
                'valid' => false,
                'validation_errors' => $form->getMessages(),
            ];
            return new JsonModel($response);
        }
        // all good
        $form_values = $form->getData();
        $form_values['page'] = $page;
        $this->session->search_defaults = $form_values;
        $results = $repository->search($form_values,$page);
        $view = new ViewModel(compact('results','deadline','csrf'));
        $is_xhr = $this->getRequest()->isXmlHttpRequest();
        if (!$is_xhr) {
            $view->setVariables(['form' => $form,]);
        } else {
            $view->setTemplate('index/results')
                 ->setTerminal(true);
        }

        return $view;
    }

    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $repo = $this->objectManager->getRepository(Entity\Request::class);
        $user = $this->objectManager->getRepository(User::class)->getUser($this->getIdentity()->id);
        return new ViewModel(['count'=> $repo->count(['submitter'=>$user->getPerson()])]);
    }

    /**
     * help.
     *
     * @return ViewModel
     */
    public function helpAction()
    {
        return new ViewModel();
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
        $paginator = $repo->list($this->auth->getIdentity(), $page);
        if ($paginator) {
            $data = $paginator->getCurrentItems()->getArrayCopy();
            // wish we were kidding, but...
            $ids = array_column(array_column($data, 0), 'id');
            $defendants = $repo->getDefendants($ids);
        }

        $deadline = $this->getTwoBusinessDaysAfterDate(new \DateTime);
        $csrf = (new \Zend\Validator\Csrf('csrf'))->getHash();
        $view = new ViewModel(compact('paginator', 'defendants', 'deadline', 'csrf'));
        $view->setTerminal($this->getRequest()->isXmlHttpRequest());

        return $view;
    }
}
