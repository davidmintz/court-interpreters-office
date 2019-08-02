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
use InterpretersOffice\Requests\Form;

use InterpretersOffice\Service\DateCalculatorTrait;

use Zend\Mvc\MvcEvent;
use Zend\Http\Request;

/**
 *  IndexController for Requests module
 *
 */
class IndexController extends AbstractActionController //implements ResourceInterface
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
    public function __construct(
        ObjectManager $objectManager,
        AuthenticationServiceInterface $auth
    ) {
        $this->objectManager = $objectManager;
        $this->auth = $auth;
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
     * view Request details
     *
     * @return Array
     */
    public function viewAction()
    {
        $id = $this->params()->fromRoute('id');
        $repository = $this->objectManager->getRepository(Entity\Request::class);
        $csrf = (new \Zend\Validator\Csrf('csrf'))->getHash();
        return [
            'data' => $repository->view($id),
            'deadline' => $this->getTwoBusinessDaysAfterDate(new \DateTime),
            'csrf' => $csrf,
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
        $repo = $this->objectManager->getRepository(Entity\Request::class);
        return new ViewModel(['count'=> $repo->count()]);
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
