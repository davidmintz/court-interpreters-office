<?php

/** module/InterpretersOffice/src/Controller/AuthController.php   */

namespace InterpretersOffice\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Authentication\AuthenticationServiceInterface;

/**
 * controller for managing user authentication
 * 
 * to be continued
 *
 */
class AuthController extends AbstractActionController {
    
    /** 
     * authentication service
     * 
     * @var AuthenticationServiceInterface 
     */
    protected $auth;
    
    /**
     * constructor
     * 
     * @param AuthenticationServiceInterface $auth
     */
    public function __construct(AuthenticationServiceInterface $auth) {
        $this->auth = $auth;
    }
    
    /**
     * index action - to be developed or removed
     * 
     * @return boolean
     */
    public function indexAction() {
        
        echo "shit is working in AuthController indexAction<br>";
        return false;
        
    }

    public function loginAction()
    {
        $form = new \InterpretersOffice\Form\LoginForm();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (! $form->isValid()) {
                return ['form' => $form];
            }
            $data = $form->getData();
            $this->auth->getAdapter()
                 ->setIdentity($data['identity'])
                 ->setCredential($data['password']);
            $result = $this->auth->authenticate();
            if ($result->isValid()) {
                $this->redirect()->toRoute('home');
            } else {
                return ['form'=>$form,'status'=> $result->getCode()];
            }
        }
    }
    /**
     * quick and dirty test action, to be removed
     * 
     * @return boolean
     */
    public function testAction()
    {
        $adapter = $this->auth->getAdapter();
        $adapter->setIdentity('david')->setCredential('boink');
        $result = $this->auth->authenticate();
        echo "testing authentication... result is an instance of ";
        echo get_class($result),'<br>';
        echo $result->isValid() ? "auth OK" : "auth FAILED";
        
        return false;
    }
    
}
