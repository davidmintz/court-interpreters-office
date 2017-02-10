<?php

/** module/Admin/src/Controller/UsersController.php */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Entity\User;

use Zend\Permissions\Acl\AclInterface;

use Zend\EventManager\EventManagerInterface;

//use InterpretersOffice\Service\Authentication\AuthenticationAwareInterface;

use Zend\Authentication\AuthenticationService;

use Zend\Session\Container as Session;

use InterpretersOffice\Admin\Form\UserForm;

/**
 * controller for admin/users.
 *
 * things we need to do here:
 *
 *   * supply a way to browse and edit existing users
 *   * add new user: encourage (require?) looking up existing person first.
 *     autocompletion?
 *    
 */
class UsersController extends AbstractActionController //implements AuthenticationAwareInterface

{

    /**
     * entity manager.
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var AclInterface
     */
    protected $acl;

    /**
     * @var AuthenticationService
     * 
     */
    protected $auth;

    /**
     * constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)//, AclInterface $acl
    {
        $this->entityManager = $entityManager;
        //$this->acl = acl;
    }
    /**
     * implements AuthenticationAwareInterface
     * 
     * @param AuthenticationService $auth
     * 
     */
    public function setAuthenticationService(AuthenticationService $auth)
    {
        $this->auth = $auth;
    }


   /**
     * attaches event handlers
    * 
    * NOTE: we might take this out after all (not necessary)
    * 
    * https://mwop.net/blog/2012-07-30-the-new-init.html
    */
    public function setEventManager(EventManagerInterface $events)
    {
        
        $acl = $this->acl;
        $events->attach('update-role', function ($e) use ($acl) {
            $acl = $serviceManager->get('acl');
            $params = $e->getParams();
            // an assertion will be needed here
            // $isAllowed = $acl->checkAcl($params['role'],$params['resource'],$params['action']);
            if (false) { // if access denied
                $controller = $e->getTarget();
                $message = $acl->getMessage() ?: "Access denied.";
                $controller->flashMessenger()->addWarningMessage($message);
                $controller->redirect()->toRoute('requests');
                return;
            }
        });
        return parent::setEventManager($events);
    }
    /**
     * add a new user
     */
    public function addAction()
    {
        $viewModel = new ViewModel(['title' => 'add a user']);
        $viewModel->setTemplate('interpreters-office/admin/users/form');
        $request = $this->getRequest();
        //$user = $this->auth->getIdentity();
        //echo get_class($user); $this->entityManager->merge($user);
        
        //echo $user->getRole();
        $form = new UserForm($this->entityManager,[
            'action'=>'create',
            'role' => (new Session('Authentication'))->role,
            //'auth'=>$this->auth
            ]
         );
        $viewModel->form = $form;
        
        return $viewModel;
    }

    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        //echo "it works"; return false;
        
        return new ViewModel(['title' => 'admin | users']);
    }
}
