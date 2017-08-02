<?php
/**
 * module/Vault/src/Service/Vault.php
 */

namespace SDNY\Vault\Service;

use Zend\Http\Client;

use Zend\Crypt\BlockCipher;
use Zend\Crypt\Symmetric\Openssl;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;

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

class Vault extends Client implements EventManagerAwareInterface {
    
    use EventManagerAwareTrait;
    
    /**
     * event manager
     * 
     * @var EventManagerInterface
     */
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
     * cipher (key) for symmetrical encryption/decryption
     * 
     * @var string
     */
    private $key;
    
    /**
     * Blockcipher for encryption/decryption
     * 
     * @var BlockCipher 
     */
    private $blockCipher;
    
    /**
     * vault authentication token
     * 
     * @var string
     */
    private $token;

    /**
     * vault address
     * 
     * @var string
     */
    private $vault_address;
    
    /**
     * Vault API prefix
     * 
     * @var string
     */
    private $prefix = '/v1';


    /**
     * path to the ultimate secret
     * 
     * @var string
     */
    private $path_to_secret;
    
    /**
     * constructor
     * 
     * @param array $config
     */
    public function __construct(Array $config) {
        
        $this->vault_address = $config['vault_address'] . $this->prefix;
        $this->path_to_secret = isset($config['path_to_secret']) ? 
            $config['path_to_secret'] : null;
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
     * sets path to secret
     * 
     * @param string
     */
    public function setPathToSecret($path)
    {
        $this->path_to_secret = $path;       
    }

    /**
     * gets path to secret
     * 
     * @return string
     */
    public function getPathToSecret()
    {
        return $this->path_to_secret;
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
     * checks response for errors
     * 
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
     * attempts Vault TLS authentication
     * 
     * this will attempt to authenticate using TLS certificates, which have to 
     * have been installed and set in our configuration up front. 
     * 
     * @link https://www.vaultproject.io/docs/auth/cert.html
     * 
     * @return Vault
     * @throws VaultException
     */
    public function authenticateTLSCert()
    {
        try {
            $this->setMethod('POST')
                ->setUri($this->vault_address .'/auth/cert/login')
                ->send();        
            $response = $this->responseToArray($this->getResponse()->getBody());
            if ($this->isError($response)) {
                $this->getEventManager()->trigger(__FUNCTION__, $this, []);
                throw new VaultException($response['errors'][0]);
            }
            $this->token = $response['auth']['client_token'];
            //printf("DEBUG: \$this->token has been set to: $this->token in %s\n",__FUNCTION__);
            return $this;
        } catch (\Exception $e) {
            throw new VaultException('could not authenticate via TLS: '.
                    $e->getMessage(),
                    $e->getCode(), $e);
        }
        
    }

    /**
     * Attempts to acquire access token that is authorized to read the cipher 
     * we use for symmetrical encryption/decryption of sensitive Interpreter 
     * data.
     * 
     * @return Vault
     * @throws VaultException
     */
    public function requestCipherAccessToken()
    {        
        //printf("DEBUG: \$this->token has been set to: $this->token in %s\n",__FUNCTION__);
        $this->getRequest()->getHeaders()
                ->addHeaderLine("X-Vault-Token: $this->token")
                ->addHeaderLine("X-Vault-Wrap-TTL: 10s");
        $endpoint = $this->vault_address . '/auth/token/create/read-cipher';
        $this->getRequest()->setContent(json_encode(
               [
                // maybe reconsider these settings
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
        return $this;             
    }
       
    
    /**
     * unwraps a wrapped response and returns it
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
                'message' => 'failed to unwrap response'
            ]);
            throw new VaultException($response['errors'][0]);
        }
        if (isset($response['auth'])) {
            $this->setAuthToken($response['auth']['client_token']);
        }        
        return $response;      
    }
    
    /**
     * requests response-wrapped encryption key
     * 
     * @param string $token authentication token
     * @return Vault
     * @throws VaultException
     */
    public function requestWrappedEncryptionKey()
    {
        if (! $this->path_to_secret) {
            throw new VaultException('path to secret has to be set before calling '.__FUNCTION__);
        }
        $endpoint = $this->vault_address . $this->path_to_secret;        
        $this->getRequest()->getHeaders()->addHeaderLine("X-Vault-Wrap-TTL: 10s");
        $this->setMethod('GET')->setUri($endpoint)->send();
        $response = $this->responseToArray($this->getResponse()->getBody());
         if ($this->isError($response)) {
            $this->getEventManager()->trigger(__FUNCTION__, $this, [
                'message' => 'failed to get wrapped encryption-key response'
            ]);
            throw new VaultException($response['errors'][0]);
        }
        $this->setAuthToken($response['wrap_info']['token']);
        return $this;  
        
    }
    
    /**
     * gets encryption key.
     *
     * convenience method that wraps the several 
     * steps into one.
     *
     * @return Vault
     * @throws VaultException
     */
    public function getEncryptionKey()
    {
        if (! $this->key) {
            $this->authenticateTLSCert()
                ->requestCipherAccessToken()
                ->unwrap();
            $this->requestWrappedEncryptionKey();
            $this->key = $this->unwrap()['data']['cipher'];
        }
        
        return $this->key;
        
    }    
    
    /**
     * gets BlockCipher
     * 
     * @return BlockCipher
     */
    protected function getBlockCipher()
    {
        if (! $this->blockCipher) {
            $this->blockCipher = new BlockCipher(new Openssl());
        }
        return $this->blockCipher;
    }
    
    /**
     * decrypts an encrypted string
     * 
     * @param string $string the encrypted datum
     * @throws VaultException
     * @return string
     */
    public function decrypt($string) {
        $cipher = $this->getBlockCipher();
        $cipher->setKey($this->getEncryptionKey());
        return $cipher->decrypt($string);
    }
    
    /**
     * encrypts a string
     * 
     * @param string $string
     * @return string
     * @throws VaultException
     */
    public function encrypt($string)
    {
        $key = $this->getEncryptionKey();
        $cipher = $this->getBlockCipher();
        $cipher->setKey($key);
        return $cipher->encrypt($string);
    }
    
    /**
     * sets Vault authentication token header
     * and instance variable
     * 
     * @param string $token
     * @return \SDNY\Service\Vault
     */
    public function setAuthToken($token)
    {
        $this->getRequest()
            ->getHeaders()
            ->addHeaderLine("X-Vault-Token:$token");
        $this->token = $token;
        return $this;
    }
    /**
     * returns Authentication token
     * 
     * @return string
     */
    public function getAuthToken()
    {
        return $this->token;
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
     * method after all, so this method is not currently used.
     * @link https://www.vaultproject.io/docs/auth/userpass.html
     * 
     * @param string $user
     * @param string $password
     * @return array Vault response as array
     */
    public function authenticateUser($user,$password)
    {
        $uri = $this->vault_address . "/auth/userpass/login/$user";
        $this->getRequest()->setContent(json_encode(['password'=>$password]));
        $this->setUri($uri)->setMethod('POST')->send();
        
        return $this->responseToArray($this->getResponse()->getBody());  
        
    }
    
}
