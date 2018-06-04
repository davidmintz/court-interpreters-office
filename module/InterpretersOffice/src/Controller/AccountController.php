<?php
/**
 * module/InterpretersOffice/src/Controller/AccountController.php.
 */

namespace InterpretersOffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Form\UserForm;
use InterpretersOffice\Entity;

use Zend\Authentication\AuthenticationServiceInterface;

use InterpretersOffice\Form\User\RegistrationForm;

/**
 *  AccountController.
 *
 *  For registration, password reset and email verification.
 *  Very much incomplete.
 */

class AccountController extends AbstractActionController
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
     * @var AuthenticationServiceInterface
     */
    protected $auth;

    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param AuthenticationServiceInterface
     */
    public function __construct(ObjectManager $objectManager, AuthenticationServiceInterface $auth)
    {
        $this->objectManager = $objectManager;
        $this->auth = $auth;
    }

    /**
     * index action
     * @return ViewModel
     */
    public function indexAction()
    {
        return new ViewModel();
    }
    /**
     * registers a new user account
     *
     * @return ViewModel
     */
    public function registerAction()
    {
        $form = new \InterpretersOffice\Admin\Form\UserForm($this->objectManager,['action'=>'create','auth_user_role'=>'anonymous']);
        $form = new RegistrationForm($this->objectManager,['action'=>'create','auth_user_role'=>'anonymous']);
        $user = new Entity\User();
        //$person = new Entity\Person();
        $form->bind($user);//->setPerson($person)
        $request = $this->getRequest();
        if (! $request->isPost()) {
            return new ViewModel(['form'=>$form]);
        }

        $data = $request->getPost();
        printf("<pre>%s</pre>",print_r($_POST,true));
        $form->setData($data);
        if (! $form->isValid()) {
            print_r($form->getMessages());
        } else { echo "valid?";}
        return new ViewModel(['form'=>$form]);
    }

    /**
     * email verification
     * s
     * @return ViewModel
     */
    public function verifyEmailAction()
    {
        return new ViewModel();
    }

    /**
     * handles password-reset requests
     *
     * @return ViewModel
     */
    public function requestPasswordAction()
    {

        return new ViewModel();
    }

    /**
     * handles actual resetting of the user's password
     *
     * @return ViewModel
     */
    public function resetPasswordAction()
    {
        return new ViewModel();
    }
    /**
     * edit account profile
     *
     * @return ViewModel
     */
    public function editAction()
    {
        if (! $this->auth->hasIdentity()) {
            $this->redirect()->toRoute('auth');
            return;
        }
        return new ViewModel();
    }
}
