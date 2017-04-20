<?php
/**
 * module/Vault/src/Service/Vault.php
 */

namespace SDNY\Vault\Service;
use Zend\Http\Client;

/**
 * A stab at a basic Vault client
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
     * additional configuration options
     * 
     * @var Array 
     */
    protected $config;
    
    /**
     * constructor
     * 
     * @param array $config
     */
    public function __construct(Array $config) {
        
        $this->vault_address = $config['vault_address'] . $this->prefix;
        unset($config['vault_address']);
        $this->config = $config;
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
     * userpass auth backend
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
        $adapter = $this->client->getAdapter()->setOptions($this->config['curl_options']);
        
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
}
