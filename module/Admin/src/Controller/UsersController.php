<?php

/** module/Admin/src/Controller/UsersController.php */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Entity\User;

use Zend\Permissions\Acl\AclInterface;

use Zend\EventManager\EventManagerInterface;

use InterpretersOffice\Service\Authentication\AuthenticationAwareInterface;
use Zend\Authentication\AuthenticationService;

/**
 * controller for admin/users.
 *
 * things we need to do here:
 *
 *   * supply a way to browse and edit existing users
 *   * add new user: encourage (require?) looking up existing person first.
 *     autocompletion.
 *   * ACL has to be in play. only admin can elevate manager to admin
 */
class UsersController extends AbstractActionController
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
     * https://mwop.net/blog/2012-07-30-the-new-init.html
     */
    public function setEventManager(EventManagerInterface $events)
    {
        
        $acl = $this->acl;
        $events->attach('update-role', function ($e) use ($acl) {
            $acl = $serviceManager->get('acl');
            $params = $e->getParams();
            // an assertion will be needed here
            //$isAllowed = $acl->checkAcl($params['role'],$params['resource'],$params['action']);
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
