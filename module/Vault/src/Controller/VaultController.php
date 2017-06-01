<?php
/**
 * module/Vault/src/Controller/VaultController
 */
namespace SDNY\Vault\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

use SDNY\Vault\Service\Vault;

/**
 * VaultController
 */
class VaultController extends AbstractActionController {
    
    
    protected $vaultService;
    
    public function __construct(Vault $vaultService) {
        
        $this->vaultService = $vaultService;
    }
    
    public function testAction()
    {
         return new JsonModel(['result'=>'OK']);
    }
    
    public function authenticateAppAction()
    {
        $json = $this->vaultService->authenticateTLSCert();
        return new JsonModel(json_decode($json,JSON_OBJECT_AS_ARRAY));
    }
    
}
