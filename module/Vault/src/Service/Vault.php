<?php
/**
 * module/Vault/src/Service/Vault.php
 */
declare(strict_types=1);

namespace SDNY\Vault\Service;

use Laminas\Http\Client;

use Laminas\Crypt\BlockCipher;
use Laminas\Crypt\Symmetric\Openssl;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerAwareTrait;

use function basename;
use function key_exists;
use function json_encode;
use function json_decode;
use function is_string;

/**
 * Extension of Laminas\Http\Client for communciating with Hashicorp Vault
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

class Vault extends Client implements EventManagerAwareInterface
{

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
        'ssl_cert' => \CURLOPT_SSLCERT,
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
     * path to access token
     *
     * @var string
     */
    private $path_to_access_token;

    /**
     * constructor
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        foreach (['vault_address','path_to_secret','path_to_access_token'] as $setting) {
            if (! isset($config[$setting]) or ! is_string($config[$setting])) {
                throw new \RuntimeException("missing/invalid configuration value for '$setting'");
            } else {
                $this->$setting = $config[$setting];
            }
        }
        $this->vault_address .= $this->prefix;

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
     * gets Vault /sys/health response
     *
     * @return Array
     */
    public function health() : Array
    {
        $this->setMethod('GET')
            ->setUri($this->vault_address .'/sys/health')
            ->send();
        $response = $this->responseToArray($this->getResponse()->getBody());

        return $response;
    }

    /**
     * sets path to secret
     *
     * @param string
     */
    public function setPathToSecret($path) : Vault
    {
        $this->path_to_secret = $path;

        return $this;
    }

    /**
     * gets path to secret
     *
     * @return string
     */
    public function getPathToSecret() : string
    {
        return $this->path_to_secret;
    }

    /**
     * gets the vault address
     *
     * @return string
     */
    public function getVaultAddress() : string
    {
        return $this->vault_address;
    }


    /**
     * checks response for errors
     *
     * @param Array $response
     * @return boolean true if error
     */
    public function isError(array $response) : bool
    {
        return key_exists('errors', $response);
    }

    /**
     * resets request, response, etc, and restores
     * request header for JSON responses
     *
     * @return \SDNY\Vault\Service\Vault
     */
    public function reset() : Vault
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
    public function authenticateTLSCert() : Vault
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
            throw new VaultException(
                'could not authenticate via TLS: '.
                    $e->getMessage(),
                $e->getCode(),
                $e
            );
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
    public function requestCipherAccessToken() : Vault
    {
        //printf("DEBUG: \$this->token has been set to: $this->token in %s\n",__FUNCTION__);
        $this->getRequest()->getHeaders()
                ->addHeaderLine("X-Vault-Token: $this->token")
                ->addHeaderLine("X-Vault-Wrap-TTL: 10s");
        $endpoint = $this->vault_address . $this->path_to_access_token; //'/auth/token/create/read-secret';
        $this->getRequest()->setContent(json_encode(
            [
                // maybe reconsider these settings
                'ttl' => '5m',
                'num_uses' => 3,
               ]
        ));
        //printf("\n DEBUG: %s\n","endpoint: $endpoint");
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
    public function unwrap() : Array
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
    public function requestWrappedEncryptionKey() : Vault
    {

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
    public function getEncryptionKey() : string
    {
        if (! $this->key) {
            $x = $this->authenticateTLSCert()
                ->requestCipherAccessToken()
                ->unwrap();

            $this->requestWrappedEncryptionKey();
            $data = $this->unwrap()['data'];
            if (key_exists('cipher',$data)) {
                $this->key = $data['cipher'];
            } else {
                // it may be nested a level deeper, depending on Vault version
                $name = basename($this->path_to_secret);
                if (key_exists($name,$data)){
                    $this->key = $data[$name]['cipher'];
                }
            }
            if (! is_string($this->key)) {
                throw new VaultException(__FUNCTION__ . ' could not get encryption/decryption key');
            }
        }

        return $this->key;
    }

    /**
     * gets BlockCipher
     *
     * @return BlockCipher
     */
    protected function getBlockCipher() : BlockCipher
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
    public function decrypt(string $string) : string
    {
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
    public function encrypt($string) : string
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
    public function setAuthToken(string $token) : Vault
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
    public function getAuthToken() : string
    {
        return $this->token;
    }

    /**
     * converts json to array
     *
     * @param string $json
     * @return Array
     */
    public function responseToArray(string $json) : Array
    {
        return json_decode($json, true);
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
    public function authenticateUser(string $user, string $password) : Array
    {
        $uri = $this->vault_address . "/auth/userpass/login/$user";
        $this->getRequest()->setContent(json_encode(['password' => $password]));
        $this->setUri($uri)->setMethod('POST')->send();

        return $this->responseToArray($this->getResponse()->getBody());
    }

}
