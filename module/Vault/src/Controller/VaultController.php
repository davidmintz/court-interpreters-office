<?php
/**
 * module/Vault/src/Controller/VaultController
 */
namespace SDNY\Vault\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

use SDNY\Vault\Service\Vault;
use SDNY\Vault\Service\VaultException;
use Zend\Authentication\AuthenticationServiceInterface;



//use Zend\Session\SessionManager;
use Zend\Crypt\BlockCipher;
use Zend\Crypt\Symmetric\Openssl;

/**
 * 
 * VaultController. very much a work-in-progress
 * 
 */
class VaultController extends AbstractActionController {
    
    
    protected $vaultService;
    
    protected $auth;
    
    public function __construct(Vault $vaultService, AuthenticationServiceInterface $auth) {
        
        $this->vaultService = $vaultService;
        $this->auth = $auth;
    }
  
    public function testAction()
    {
      
        $this->verifyAuth();
        echo " ...gack! looking good in testAction ..." ;  
        return false;
        return new JsonModel(['result'=>'OK']);
    }
    /**
     * verifies CSRF token and re-verifies authentication/authorization
     * 
     * @return boolean
     * @throws VaultException
     */
    protected function verifyAuth(Array $params) {
        
        if (! $this->auth->hasIdentity()) {
            throw new VaultException("authentication is required");
        }
        if (! isset($params['csrf'])) {
            throw new VaultException("missing CSRF token");
        }
        $validator = new \Zend\Validator\Csrf(['name'=>'csrf']);        
        if ( ! $validator->isValid($params['csrf'])) {
             throw new VaultException("invalid CSRF token");
        }
        $role = (string)$this->auth->getIdentity()->role;
        if (!in_array($role,['administrator','manager'])) {
            throw new VaultException("authorization denied to user in role $role");
        }
        return true;
    }   
    
    

    public function decryptAction()
    {
        $params = $this->params()->fromPost();
       
        try {
            
            $this->verifyAuth($params);             
            $key = $this->vaultService->getEncryptionKey();
            $cipher = new BlockCipher(new Openssl());
            $cipher->setKey($key);
            $decrypted = [];
            foreach(['ssn','dob'] as $field) {
                if (empty($params[$field])) {
                     $decrypted[$field] = '';
                     continue;
                }
                $decrypted[$field] =  $cipher->decrypt($params[$field]);
            }            
            return new JsonModel($decrypted) ;
            
        } catch (VaultException $e) {

             return new JsonModel(['error'=>$e->getMessage()]);
        }
    }  
}
