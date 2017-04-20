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
     * attempts to authenticate a user
     * 
     * this will attempt to  authenticate user against Vault's
     * userpass auth backend
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
        $json = $this->client->getResponse()->getBody();
        return $json;
        
    }
    
}
