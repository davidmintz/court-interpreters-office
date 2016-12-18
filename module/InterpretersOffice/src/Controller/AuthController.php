<?php

/** module/InterpretersOffice/src/Controller/AuthController.php   */

namespace InterpretersOffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\View\Model\ViewModel;

/**
 * controller for managing user authentication.
 *
 * to be continued
 */
class AuthController extends AbstractActionController
{
    /**
     * authentication service.
     *
     * @var AuthenticationServiceInterface
     */
    protected $auth;

    /**
     * constructor.
     *
     * @param AuthenticationServiceInterface $auth
     */
    public function __construct(AuthenticationServiceInterface $auth)
    {
        $this->auth = $auth;
    }

    /**
     * index action - to be developed or removed.
     *
     * @return bool
     */
    public function indexAction()
    {
        echo 'shit is working in AuthController indexAction<br>';

        return false;
    }
    /**
     * login action.
     * 
     * on a GET request or POSTed failed authentication, returns a 
     * view; otherwise, redirect to admin main page or to main front
     * page, depending on authenticated user's role.
     * 
     * @return ViewModel
     */
    public function loginAction()
    {
        $form = new \InterpretersOffice\Form\LoginForm();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (!$form->isValid()) {
                return new ViewModel(['form' => $form]);
            }
            $data = $form->getData();
            $this->auth->getAdapter()
                 ->setIdentity($data['identity'])
                 ->setCredential($data['password']);
            $result = $this->auth->authenticate();
            if (! $result->isValid()) {
                 return new ViewModel(
                        ['form' => $form, 'status' => $result->getCode()]
                );
            }
            $user = $this->auth->getIdentity();
            // managers and administrators go to /admin
            if (in_array((string)$user->getRole(),['administrator','manager'])) {
                $route = 'admin';
            } else {
                // everyone else goes to the main page
                $route = 'home';
            }
            $this->events->trigger(__FUNCTION__,$this,[
                'user' => $user,
            ]);
            $this->redirect()->toRoute($route);
        }

        return new ViewModel(['form' => $form]);
    }

    /**
     * logout action.
     */
    public function logoutAction()
    {
        if ($this->auth->hasIdentity()) {
            $this->auth->clearIdentity();
            $this->flashMessenger()
                 ->addSuccessMessage('You have logged out');
        } else {
            $this->redirect()->toRoute('home');
        }
        $this->redirect()->toRoute('auth');
    }
}
