<?php
/** module/Admin/src/Controller/UsersController.php */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Doctrine\ORM\EntityManagerInterface;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventInterface;
use Zend\Authentication\AuthenticationService;

//use Zend\Session\Container as Session;

use InterpretersOffice\Admin\Form\UserForm;
use InterpretersOffice\Entity;
use InterpretersOffice\Service\Authentication\AuthenticationAwareInterface;

use InterpretersOffice\Admin\Service\Acl;

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
class UsersController extends AbstractActionController implements AuthenticationAwareInterface
{

    /**
     * entity manager.
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * the role of the currently authenticated user
     * @var string $auth_user_role
     */
    protected $auth_user_role;
    
    /**
     * acl
     * 
     * @var Acl
     */
    protected $acl;

    /**
     * constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        
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
        $this->auth_user_role = $auth->getIdentity()->role;        
    }


   /**
    * attaches event handlers
    *
    * Before they try to create a user account, if they're attaching the account
    * to a Person that already exists, this checks the Person's Hat property for validity.
    * We won't expose links that lead to an invalid situation, but a person could
    * manually edit the query parameters.
    *
    * If they do load a valid Person in the addAction(), we check that an associated
    * User account does not already exist.
    *   
    *
    * @param EventManagerInterface events
    */
    public function setEventManager(EventManagerInterface $events)
    {
       
        $entityManager = $this->entityManager;
        $role_id = $this->auth_user_role; 
        $events->attach('load-person', function (EventInterface $e) 
                use ($entityManager,$role_id) {

            $person = $e->getParam('person');
            $hat = $person->getHat();
            $form = $e->getParam('form');
            $hats_allowed = $form->get('user')->get('person')->get('hat')
                    ->getValueOptions();
            if (! in_array($hat->getId(), array_column($hats_allowed, 'value')))
            {
                $errorMessage =
                sprintf(
                    'The person identified by id %d, %s %s, wears the hat %s, '
                      . 'but people in that category do not have user accounts '
                      . 'in this system.',
                    $person->getId(),
                    $person->getFirstName(),
                    $person->getLastname(),
                    $hat
                );
                $controller = $e->getTarget();
                $controller->flashMessenger()->addErrorMessage($message);
                return  $controller->redirect()->toRoute('users');
            }
            $action = $e->getTarget()->params()->fromRoute('action');
            if ('add' == $action) {
                // is there already a User account?
                $user = $entityManager->getRepository('InterpretersOffice\Entity\User')
                        ->findOneBy(['person' => $person]);
                if ($user) {
                   $container = $e->getTarget()->getEvent()->getApplication()
                           ->getServiceManager();
                  
                   $message = sprintf(
                      'We can\'t create a new user account because the person '
                            . 'whose id is %d (%s %s) already has one. ',
                            $person->getId(),
                            $person->getFirstname(),
                            $person->getLastname());
                   
                   $resource_id = $user->getResourceId();
                   $acl = $e->getTarget()->getEvent()->getApplication()
                    ->getServiceManager()->get('acl');
                   $can_edit = $acl->isAllowed($role_id,$resource_id);                                              
                   if ($can_edit) {
                       $helper = $container->get('ViewHelperManager')->get('url');
                       $url = $helper('users/edit',['id'=>$user->getId()]);
                       $message .= sprintf(
                         'You can <a href="%s">edit it</a> if you want to.',
                               $url
                          );                      
                   }
                   $controller = $e->getTarget();
                   $controller->flashMessenger()->addErrorMessage($message);
                   return  $controller->redirect()->toRoute('users');
                }
            } 
        });        
        // ACL enforcement       
        $events->attach('load-user', function (EventInterface $e) use ($role_id)
        {          
            $resource_id = $e->getParam('user')->getResourceId();
             $acl = $e->getTarget()->getEvent()->getApplication()
                    ->getServiceManager()->get('acl');
            if (! $acl->isAllowed($role_id,$resource_id)) {
                $controller = $e->getTarget();
                $message = 'Access denied to administrator\'s user account';
                $controller->getEventManager()->trigger(
                                       Acl::class,'access-denied',                             
                                [   'role'=>$role_id,
                                    'resource'=>$resource_id, 
                                    'privilege'=>$controller->params()->fromRoute('action')
                                ]
                                 );
                
                $controller->flashMessenger()->addErrorMessage($message);
                return  $controller->redirect()->toRoute('users');
            }
        });
         parent::setEventManager($events);        
    }
    
    /**
     * add a new user
     */
    public function addAction()
    {
        $viewModel = new ViewModel(['title' => 'add a user']);
        $viewModel->setTemplate('interpreters-office/admin/users/form');
        $form = new UserForm($this->entityManager, [
            'action' => 'create',
            'auth_user_role' => $this->auth_user_role,
            ]);
        $user = new Entity\User();

        // if they are trying to add a user account for an existing person...
        $person_id = $this->params()->fromRoute('id');
        if ($person_id) {
            $person = $this->entityManager
                    ->find('InterpretersOffice\Entity\Person', $person_id);
            if (! $person) {
                return $viewModel->setVariables(
                    ['errorMessage' => "person with id $person_id not found"]);
            }
            $this->events->trigger('load-person', $this, 
                    compact('person', 'form'));            
            $user->setPerson($person);
            $form->get('user')->get('person')->setObject($person);
        }

        $form->bind($user);
        $viewModel->form = $form;
        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (! $form->isValid()) {
                //echo "not valid.<pre>"; print_r($form->getMessages());echo "</pre>";
                return $viewModel;
            }
            $this->entityManager->persist($user);
            if (! $person_id) {
                $this->entityManager->persist($user->getPerson());
            }
            $user->setPassword(bin2hex(openssl_random_pseudo_bytes(8)));
            $this->entityManager->flush();
            $person = $user->getPerson();
            $this->flashMessenger()->addSuccessMessage(
                sprintf(
                    'A user account has been created for %s <strong>%s %s</strong>.',
                    $user->getRole(),
                    $person->getFirstName(),
                    $person->getLastname()
                )
            );
            $this->redirect()->toRoute('users');
        }
        return $viewModel;
    }

    /**
     * edits an existing user account
     */
    public function editAction()
    {

        $id = $this->params()->fromRoute('id');
        $viewModel = (new ViewModel(['title' => 'edit a user','id' => $id]))
                ->setTemplate('interpreters-office/admin/users/form');
        $user = $this->entityManager->find('InterpretersOffice\Entity\User', $id);
        if (! $user) {
            return $viewModel->setVariables(['errorMessage' => 
                "user with id $id was not found in your database."]);
        }
        $this->events->trigger('load-user',$this,['user'=>$user,]);
        $form = new UserForm($this->entityManager, [
            'action' => 'update',
            'auth_user_role' => $this->auth_user_role,
            ]);
        $person = $user->getPerson();
        /** @todo do this initialization somewhere else?  */
        $form->get('user')->get('person')->setObject($user->getPerson());
        /* -------------------------- */
        $viewModel->form = $form;
        $form->bind($user);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (! $form->isValid()) {                
                return $viewModel;
            }            
            $this->entityManager->flush(); // return $viewModel;
            $this->flashMessenger()
                  ->addSuccessMessage(sprintf(
                      'The user account for <strong>%s %s</strong> has been updated.',
                      $person->getFirstname(),
                      $person->getLastname()
                  ));
            $this->redirect()->toRoute('users');
        }
        return $viewModel;
    }

    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        //echo ($this->params()->fromRoute('action'));

        return new ViewModel(['title' => 'admin | users','role' => $this->auth_user_role]);
    }
}
