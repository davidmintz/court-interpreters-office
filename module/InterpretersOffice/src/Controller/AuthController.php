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
     * @return ViewModel
     */
    public function loginAction()
    {
        $form = new LoginForm();
        $request = $this->getRequest();
        $is_xhr = $request->isXmlHttpRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (! $form->isValid()) {
                return $is_xhr ?
                        new JsonModel([
                            'validation_errors' => $form->getMessages(),
                            'authenticated' => false,
                            'login_csrf' => $form->get('login_csrf')->getValue(),
                            ])
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
                return $is_xhr ? new JsonModel([
                            'authenticated' => false,
                            'error' => "authentication failed",
                        ])
                    : new ViewModel(
                        ['form' => $form, 'status' => $result->getCode()]
                    );
            }
            $user = $this->auth->getIdentity();
            $role = $user->role;

            // if they tried to load a page and were sent away, send them back
            $session = new \Zend\Session\Container('Authentication');

            // echo "DEBUG:  auth OK. not redirecting....";
            // return new ViewModel(['form' => $form, 'title' => 'user login']);
            if ($request->isXmlHttpRequest()) {
                $csrf = new \Zend\Form\Element\Csrf('csrf');
                $token = $csrf->getValue();
                return new JsonModel(['authenticated' => true,'csrf' => $token ]);
            }

            if (isset($session->redirect_url)) {
                $url = $session->redirect_url;
                unset($session->redirect_url);

                return $this->redirect()->toUrl($url);
            }
            // nice try...
            // if ($data['referrer']) {
            //     return $this->redirect()->toUrl($data['referrer']);
            // }
            // managers and administrators go to /admin
            if (in_array($role, ['administrator', 'manager'])) {
                $route = 'admin';
            } elseif ('submitter' == $role) {
                if ($this->getEvent()->getRouter()->hasRoute('requests')) {
                    $route = 'requests';
                } else {
                    $route = 'home';
                }
            } else {
                // everyone else goes to the main page
                $route = 'home';
            }
            $event_params['auth'] = $this->auth;
            $this->events->trigger(__FUNCTION__, $this, $event_params);
            $this->redirect()->toRoute($route);
        }
        $this->getResponse()->getHeaders()
            ->addHeaderLine('X-Authentication-required', "true");
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

        return $this->redirect()->toRoute('login');
    }
}
