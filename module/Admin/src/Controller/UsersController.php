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
use InterpretersOffice\Entity;

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
     * the role of the currently authenticated user
     * @var string $auth_user_role
     */
    protected $auth_user_role;

    /**
     * constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)//, AclInterface $acl
    {
        $this->entityManager = $entityManager;
        $this->auth_user_role = (new Session('Authentication'))->role;
        
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
                
        $events->attach('load-person', function ($e)  {
           
            $person = $e->getParam('person');
            $hat = $person->getHat(); 
            $form = $e->getParam('form');
            $hats_allowed = $form->get('user')->get('person')->get('hat')->getValueOptions();
            if (! in_array($hat->getId(), array_column($hats_allowed, 'value')))
            $e->getParam('viewModel')->errorMessage =
                sprintf('The person identified by id %d, %s %s, wears the hat %s, but people in that category do not have user accounts in this system.',
                    $person->getId(), $person->getFirstName(), $person->getLastname(), $hat
            );       
        }
        );
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
        $form = new UserForm($this->entityManager,[
            'action'=>'create',
            'auth_user_role' => $this->auth_user_role,           
            ]
        );
        $user = new Entity\User();
        // how to populate the person fieldset but not the user
        $person_id = $this->params()->fromRoute('id');
        if ($person_id) {
            $person = $this->entityManager->find('InterpretersOffice\Entity\Person',$person_id);
            if (! $person) {
                return $viewModel->setVariables(['errorMessage' => "person with id $person_id not found"]);
            }
            $this->events->trigger('load-person',$this,compact('person', 'form', 'viewModel'));  
            if ($viewModel->errorMessage) {
                return $viewModel;
            }
            $user->setPerson($person);
            $form->get('user')->get('person')->setObject($person);      
        }
        $viewModel->form = $form;
        
        $form->bind($user);

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (!$form->isValid()) {
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
                    $person->getRole(),
                    $person->getFirstName(),
                    $person->getLastname()
                )
            );
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
        //echo "it works"; return false;
        
        return new ViewModel(['title' => 'admin | users','role'=>$this->role]);
    }
}
