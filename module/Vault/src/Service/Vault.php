<?php
/**
 * module/Vault/src/Service/Vault.php
 */

namespace SDNY\Vault\Service;
use Zend\Http\Client;


/**
 * A stab at a basic Vault client
 * 
 * @todo consider:  maybe this should simply extend Zend\Http\Client
 * instead of wrapping one?
 */

class Vault {
    
    /**
     * client to use for speaking to vault
     * 
     * @var Zend\Http\Client
     */
    protected $client;
    
    /**
     * vault address
     * 
     * @var string
     */
    protected $vault_address;
    
    /**
     * Vault API prefix
     * 
     * @var string
     */
    protected $prefix = '/v1';
    
    /**
     * constructor
     * 
     * @param array $config
     */
    public function __construct(Array $config) {
        
        $this->vault_address = $config['vault_address'] . $this->prefix;
        unset($config['vault_address']);
        // hand off the rest of the config to Zend\Http\Client
        $this->client =  new Client(null,$config);        
        $this->client->getRequest()
            ->getHeaders()
            ->addHeaderLine('Accept: application/json');
    }
    
    /**
     * gets the vault address
     * 
     * @return string
     */
    public function getVaultAddress()
    {
        return $this->vault_address;
    }
    
    /**
     * gets client
     * 
     * returns our configured client so that the caller
     * can do whatever with it
     * @return \Zend\Http\Client
     */
    public function getClient()
    {
        return $this->client;
    }
    
    /**
     * attempts user/password authentication
     * 
     * this will attempt to authenticate user against Vault's
     * userpass auth backend.
     * NOTE: looks like we won't be using
     * this after all, so this method can disappear
     * @link https://www.vaultproject.io/docs/auth/userpass.html
     * 
     * @param string $user
     * @param string $password
     * @return string json data
     */
    public function authenticateUser($user,$password)
    {
        $uri = $this->vault_address . "/auth/userpass/login/$user";
        $this->client->getRequest()->setContent(json_encode(['password'=>$password]));
        $this->client
                ->setUri($uri)                
                ->setMethod('POST')
                ->send();
        
        return $this->client->getResponse()->getBody();
        
    }
    
    /**
     * attempts Vault TSL authentication
     * 
     * this will attempt to authenticate using TLS certificates
     * @link https://www.vaultproject.io/docs/auth/cert.html
     */
    public function authenticateTLSCert($options = [])
    {
        $this->client->setMethod('POST')
            ->setUri($this->vault_address .'/auth/cert/login')
            ->send();
        
        return $this->client->getResponse()->getBody();        
    }
    
    /**
     * sets Vault authentication token header
     * 
     * @param string $token
     * @return \SDNY\Service\Vault
     */
    public function setAuthToken($token)
    {
        $this->client->getRequest()
            ->getHeaders()
            ->addHeaderLine("X-Vault-Token:$token");
        
        return $this;
    }
    
    /**
     * attempts to acquire and return Vault access token that is
     * authorized to read the cipher we use for symmetrical encryption/decryption
     * of sensitive Interpreter data.
     * 
     * @param string $auth_token 
     * @return \SDNY\Service\Vault
     */
    public function getCipherAccessToken($auth_token = null)
    {
        if ($auth_token) {
            $this->client->getRequest()
            ->getHeaders()
            ->addHeaderLine("X-Vault-Token:$auth_token");
        }
        $endpoint = $this->vault_address . '/auth/token/create/read-cipher';
        $this->client->getRequest()->setContent(json_encode(
               [
                'ttl' => '5m',
                'num_uses' => 3,
               ]
        ));
        $this->client->setMethod('POST')->setUri($endpoint)->send();
        
        return $this->client->getResponse()->getBody();           
    }
}
