<?php
/**
 * module/Vault/src/Service/Vault.php
 */

namespace SDNY\Vault\Service;

use Zend\Http\Client;


/**
 * Extension of Zend\Http\Client for communciating with Hashicorp Vault
 * 
 * The purpose is enable us to store sensitive data in MySQL using symmetrical
 * encryption while avoiding having to store the encryption key in plain text 
 * anywhere at any time. All the configuration has to be correctly set before 
 * instantiation. Error-checking is left up to the consumer.
 * 
 * Absent further precautions, it's surely still possible to beat this in a 
 * worst-case scenario, but it's a good start.
 * 
 */

use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;

class Vault extends Client implements EventManagerAwareInterface {
    
    use EventManagerAwareTrait;
    
    protected $events;
    
    /**
     * mapping of string keys to CURL integer constants
     * 
     * we need this because if a config array key is an integer
     * unfortunate things happen when the framework merges the configs
     * 
     * @var array
     */
    private static $curlopt_keys = [
        'ssl_key' => \CURLOPT_SSLKEY,
        'ssl_cert'=> \CURLOPT_SSLCERT,        
    ];
    
    
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
        $curloptions = [];
        foreach ($config as $key => $value) {
            if (key_exists($key, self::$curlopt_keys)) {
                $curloptions[self::$curlopt_keys[$key]] = $value;
            }
        }
        $config['curloptions'] = $curloptions;       

        parent::__construct(null, $config);
        $this->getRequest()
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
    
    private $token;
   

    /**
     * checks response for errors
     * @param Array $response
     * @return boolean true if error
     */
    public function isError(Array $response) {
        return key_exists('errors',$response);
    }
    
    /**
     * resets request, response, etc, and restores
     * request header for JSON responses
     * 
     * @return \SDNY\Vault\Service\Vault
     */
    public function reset()
    {
        parent::reset();
        $this->getRequest()
            ->getHeaders()
            ->addHeaderLine('Accept: application/json');
        return $this;
    }
    
    /**
     * attempts Vault TSL authentication
     * 
     * this will attempt to authenticate using TLS certificates, which have to 
     * have been installed and set in our configuration up front. 
     * 
     * @link https://www.vaultproject.io/docs/auth/cert.html
     */
    public function authenticateTLSCert($options = [])
    {
        $this->setMethod('POST')
            ->setUri($this->vault_address .'/auth/cert/login')
            ->send();        
        $response = $this->responseToArray($this->getResponse()->getBody());
        if ($this->isError($response)) {
            $this->getEventManager()->trigger(__FUNCTION__, $this, []);
            throw new VaultException($response['errors'][0]);
        }
        $this->token = $response['auth']['client_token'];
        //printf("DEBUG: \$this->token is $this->token in %s<br>",__FUNCTION__);
        return $response;
    }

    /**
     * Attempts to acquire and return response-wrapped Vault access token that is
     * authorized to read the cipher we use for symmetrical encryption/decryption
     * of sensitive Interpreter data.
     * 
     * The $auth_token parameter is used for authentication if provided; otherwise
     * it's assumed to have been set already
     *
     * @param string $auth_token 
     * @return array 
     */
    public function getCipherAccessToken($auth_token = null)
    {
        /*
        if ($auth_token) {
            $this->getRequest()->getHeaders()
            ->addHeaderLine("X-Vault-Token:$auth_token");            
        }         
         */
        $this->getRequest()->getHeaders()
                ->addHeaderLine("X-Vault-Token:$this->token")
                ->addHeaderLine("X-Vault-Wrap-TTL: 3m");
        $endpoint = $this->vault_address . '/auth/token/create/read-cipher';
        $this->getRequest()->setContent(json_encode(
               [
                'ttl' => '5m',
                'num_uses' => 3,
               ]
        ));
        $this->setMethod('POST')->setUri($endpoint)->send();
        $response = $this->responseToArray($this->getResponse()->getBody());
        if ($this->isError($response)) {
            $this->getEventManager()->trigger(__FUNCTION__, $this, [
                'message' => 'failed to get token for cipher access'
            ]);
            throw new VaultException($response['errors'][0]);
        }
        
        
        $this->token = $response['wrap_info']['token'];
        //printf("DEBUG: \$this->token is $this->token in %s<br>",__FUNCTION__);
        return $response;             
    }
       
    
    /**
     * unwraps a wrapped response
     * 
     * @param string $token
     * @return array
     */
    public function unwrap()
    {
        $this->reset();
        
        $endpoint = $this->vault_address . '/sys/wrapping/unwrap';
        $this->setAuthToken($this->token);
        $this->setMethod('POST')->setUri($endpoint)->send();
        
        $response = $this->responseToArray($this->getResponse()->getBody());
        if ($this->isError($response)) {
            $this->getEventManager()->trigger(__FUNCTION__, $this, [
                'message' => 'failed to get token for cipher access'
            ]);
            throw new VaultException($response['errors'][0]);
        }
        $this->token = $response['auth']['client_token'];
        //printf("DEBUG: \$this->token is $this->token in %s<br>",__FUNCTION__);
        return $response;      
    }
    
    /**
     * 
     * @param string $token authentication token
     * @return Array
     */
    public function getEncryptionKey()
    {
        $endpoint = $this->vault_address . '/secret/sdny/encryption';
        $this->setAuthToken($this->token);
        //$this->getRequest()->getHeaders()->addHeaderLine("X-Vault-Wrap-TTL: 3m");
        $this->setMethod('GET')->setUri($endpoint)->send();
        $response = $this->responseToArray($this->getResponse()->getBody());
         if ($this->isError($response)) {
            $this->getEventManager()->trigger(__FUNCTION__, $this, [
                'message' => 'failed to get token for cipher access'
            ]);
            throw new VaultException($response['errors'][0]);
        }
        //var_dump($response);
        return $response;  
        
    }
    
    /**
     * sets Vault authentication token header
     * 
     * @param string $token
     * @return \SDNY\Service\Vault
     */
    public function setAuthToken($token)
    {
        $this->getRequest()
            ->getHeaders()
            ->addHeaderLine("X-Vault-Token:$token");
        
        return $this;
    }
    
   
    
    /**
     * converts json to array
     * 
     * @param string $json
     * @return Array
     */
    public function responseToArray($json) {
        
        return json_decode($json,true);
    }
    
    /**
     * attempts user/password authentication
     * 
     * this will attempt to authenticate user against Vault's
     * userpass auth backend. NOTE: looks like we won't be using this auth 
     * method after all, so this method can disappear
     * @link https://www.vaultproject.io/docs/auth/userpass.html
     * 
     * @param string $user
     * @param string $password
     * @return string json data
     */
    public function authenticateUser($user,$password)
    {
        $uri = $this->vault_address . "/auth/userpass/login/$user";
        $this->getRequest()->setContent(json_encode(['password'=>$password]));
        $this->setUri($uri)->setMethod('POST')->send();
        
        return $this->responseToArray($this->getResponse()->getBody());  
        
    }
    
}
