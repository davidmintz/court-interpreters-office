<?php
/**
 * module/Vault/src/Controller/VaultController
 */
namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

use SDNY\Vault\Service\Vault;
use Zend\Authentication\AuthenticationServiceInterface;

//use Zend\Session\SessionManager;
use Zend\Crypt\BlockCipher;
use Zend\Crypt\Symmetric\Openssl;

/**
 * VaultController
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
        echo " ...gack" ; 
        $this->verifyAuth();
        return false;
        return new JsonModel(['result'=>'OK']);
    }
    /**
     * redundant authentication and authorization check
     * 
     * @return boolean
     * @throws \Exception
     */
    protected function verifyAuth() {
        if (! $this->auth->hasIdentity()) {
            throw new \Exception("authentication is required");
        }
        $role = (string)$this->auth->getIdentity()->getRole();
        if (!in_array($role,['administrator','manager'])) {
            throw new \Exception("authorization denied to user in role $role");
        }
        return true;
    }
            
    public function authenticateAppAction()
    {
        return new JsonModel(json_decode($this->vaultService->authenticateTLSCert(),JSON_OBJECT_AS_ARRAY));
    }
    
    public function decryptAction()
    {
        $params = $this->params()->fromPost();
        $cipher = new BlockCipher(new Openssl);
        $applicationAuth = json_decode($this->vaultService->authenticateTLSCert());
        $token = $applicationAuth->auth->client_token;
        
        $wrappedResponse = json_decode($this->vaultService->getCipherAccessToken($token));
        $unwrapToken = $wrappedResponse->wrap_info->token;
        $unwrappedResponse = $this->vaultService->unwrap( $unwrapToken);
        //var_dump(json_decode($unwrappedResponse)); 
        $cipherToken = json_decode($unwrappedResponse)->auth->client_token;
        //echo $cipherToken;
        $response = json_decode($this->vaultService->getEncryptionKey($cipherToken));
        $cipher = $response->data->cipher;
        echo "cipher is $cipher ";
        return false;
        
    }
  
}
