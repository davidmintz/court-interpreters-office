<?php
/**
 * module/Vault/src/Controller/VaultController
 */
namespace SDNY\Vault\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;

use SDNY\Vault\Service\Vault;
use SDNY\Vault\Service\VaultException;
use Laminas\Authentication\AuthenticationServiceInterface;



//use Laminas\Session\SessionManager;
use Laminas\Crypt\BlockCipher;
use Laminas\Crypt\Symmetric\Openssl;

/**
 *
 * VaultController. very much a work-in-progress
 *
 */
class VaultController extends AbstractActionController
{

    /**
     * client for communicating with Vault
     *
     * @var SDNY\Vault\Service\Vault
     */
    protected $vaultService;

    /**
     * auth service
     *
     * @var Laminas\Authentication\AuthenticationServiceInterface
     */
    protected $auth;

    /**
     * constructor
     *
     * @param Vault $vaultService
     * @param AuthenticationServiceInterface $auth
     */
    public function __construct(Vault $vaultService, AuthenticationServiceInterface $auth)
    {

        $this->vaultService = $vaultService;
        $this->auth = $auth;
    }

    /**
     * verifies CSRF token and re-verifies authentication/authorization
     * @param Array $params
     * @return boolean
     * @throws VaultException
     */
    protected function verifyAuth(array $params)
    {

        if (! $this->auth->hasIdentity()) {
            throw new VaultException("authentication is required");
        }
        if (! isset($params['csrf'])) {
            throw new VaultException("missing CSRF (security) token");
        }
        $validator = new \Laminas\Validator\Csrf(['name' => 'csrf']);
        if (! $validator->isValid($params['csrf'])) {
             throw new VaultException("invalid CSRF (security) token");
        }
        $role = (string)$this->auth->getIdentity()->role;
        if (! in_array($role, ['administrator','manager'])) {
            throw new VaultException("authorization denied to user in role $role");
        }
        return true;
    }

    /**
     * decrypts encrypted values in POST
     *
     * @return JsonModel
     * @throws VaultException
     */

    public function decryptAction()
    {
        $params = $this->params()->fromPost();

        try {
            $this->verifyAuth($params);
            $key = $this->vaultService->getEncryptionKey();
            $cipher = new BlockCipher(new Openssl());
            $cipher->setKey($key);
            $decrypted = [];
            foreach (['ssn','dob'] as $field) {
                if (empty($params[$field])) {
                     $decrypted[$field] = '';
                     continue;
                }
                $decrypted[$field] = $cipher->decrypt($params[$field]);
            }
            return new JsonModel($decrypted) ;
        } catch (VaultException $e) {
             return new JsonModel(['error' => $e->getMessage()]);
        }
    }
}
