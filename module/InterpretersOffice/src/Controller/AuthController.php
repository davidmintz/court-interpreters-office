<?php

/** module/InterpretersOffice/src/Controller/AuthController.php   */

namespace InterpretersOffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use InterpretersOffice\Form\LoginForm;

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
     * A string "role" is kept in the session to save on database queries that 
     * Doctrine runs when you getIdentity() from the auth object
     *
     * @return ViewModel
     */
    public function loginAction()
    {
        $form = new LoginForm();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (! $form->isValid()) {
                return $request->isXmlHttpRequest() ?
                        new JsonModel(['validation_errors'=>$form->getMessages(),'authenticated'=>false])
                        :
                         new ViewModel(['form' => $form]);
            }
            $data = $form->getData();
            $this->auth->getAdapter()
                 ->setIdentity($data['identity'])
                 ->setCredential($data['password']);
            $result = $this->auth->authenticate();
            $event_params = ['result' => $result, 'identity' => $data['identity']];
            if (! $result->isValid()) {
                $this->events->trigger(__FUNCTION__, $this, $event_params);
                
                return new ViewModel(
                    ['form' => $form, 'status' => $result->getCode()]
                );
            }
            // TMP DEBUG
            // echo (spl_object_hash($this->auth). " is the hash of our auth object in the Controller loginAction\n");
            $user = $this->auth->getIdentity();
            $role = (string) $user->getRole();          
            // if they tried to load a page and were sent away, send them back
            $session = new \Zend\Session\Container('Authentication');
            $session->role = $role;
            // TMP DEBUG
            // echo "DEBUG:  auth OK. not redirecting....";
            // return new ViewModel(['form' => $form, 'title' => 'user login']);
            if ($request->isXmlHttpRequest()) {
                return new JsonModel(['authenticated'=>true]);
            }

            if (isset($session->redirect_url)) {
                $url = $session->redirect_url;
                unset($session->redirect_url);

                return $this->redirect()->toUrl($url);
            }
            // managers and administrators go to /admin
            if (in_array($role, ['administrator', 'manager'])) {
                $route = 'admin';
            } else {
                // everyone else goes to the main page
                $route = 'home';
            }
            $this->events->trigger(__FUNCTION__, $this, $event_params);
            $this->redirect()->toRoute($route);
        }
        $this->getResponse()->getHeaders()->addHeaderLine('X-Authentication-required', "true");
        return new ViewModel(['form' => $form, 'title' => 'user login']);
    }

    /**
     * logout action.
     */
    public function logoutAction()
    {
        if ($this->auth->hasIdentity()) {
            $user = $this->auth->getIdentity();
            $this->auth->clearIdentity();
            $this->flashMessenger()
                 ->addSuccessMessage('You have logged out');
            $this->events->trigger(__FUNCTION__, $this, ['user' => $user]);
        } else {
            $this->redirect()->toRoute('home');
        }

        $this->redirect()->toRoute('auth');
    }
}
