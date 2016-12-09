<?php

/** module/InterpretersOffice/src/Controller/AuthController.php   */

namespace InterpretersOffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Authentication\AuthenticationServiceInterface;

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
     */
    public function loginAction()
    {
        $form = new \InterpretersOffice\Form\LoginForm();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (!$form->isValid()) {
                return ['form' => $form];
            }
            $data = $form->getData();
            $this->auth->getAdapter()
                 ->setIdentity($data['identity'])
                 ->setCredential($data['password']);
            $result = $this->auth->authenticate();
            if ($result->isValid()) {
                //echo "YAY !!!";
                $this->redirect()->toRoute('home');
            } else {
                return ['form' => $form, 'status' => $result->getCode()];
            }
        }

        return ['form' => $form];
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
