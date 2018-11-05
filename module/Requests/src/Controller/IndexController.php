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
use InterpretersOffice\Entity\Repository\CourtClosingRepository;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use InterpretersOffice\Admin\Service\Acl;
use InterpretersOffice\Requests\Form;

use Zend\Mvc\MvcEvent;
use Zend\Http\Request;

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
     * @param Acl $acl
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
     * onDispatch event Listener
     *
     * @param  MvcEvent $e
     * @return mixed
     */
    public function __onDispatch($e)
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
                $this->getResponse()->setStatusCode(403);
                $message = "Sorry, you are not authorized to {$params['action']}";
                if ($this->getRequest()->isXmlHttpRequest()) {
                    $data = [ 'error' => [
                                'code' => 403,
                                'message' => "$message this request.",
                            ]
                        ];
                    $response = $this->getResponse()
                        ->setContent(json_encode($data));
                    $response->getHeaders()
                            ->addHeaderline('Content-type: application/json');
                    return $response;
                }
                $viewRenderer = $e->getApplication()->getServiceManager()
                ->get('ViewRenderer');
                $url  = $this->getPluginManager()->get('url')
                ->fromRoute('requests/view',['id'=>$entity->getId()]);
                $content = $viewRenderer->render(
                    (new ViewModel())->setTemplate('denied')->setVariables([
                        'message' =>
                        "$message this <a href=\"$url\">request</a>."])
                    );
                    $viewModel = $e->getViewModel()
                    ->setVariables(['content'=>$content]);

                return $this->getResponse()
                    ->setContent($viewRenderer->render($viewModel));
            }
        }

        return parent::onDispatch($e);
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
        return [
            'data'=> $repository->view($id),
            'deadline'=>  $this->getTwoBusinessDaysFromDate(),
        ];

    }

    /**
     * search action
     *
     * @return ViewModel
     * @todo implement
     */
    public function searchAction()
    {
        return new ViewModel();
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
     * gets datetime two business days from $date.
     *
     * proxies to CourtClosingRepository::getTwoBusinessDaysFromDate()
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
}
