<?php
namespace SDNY\Vault\Test;

use ApplicationTest\AbstractControllerTest;

use SDNY\Vault\Service\Vault as VaultClient;

class VaultInitializationTest extends AbstractControllerTest 
{
    
    /**
     * vault service client
     * 
     * @var VaultClient
     */
    protected $vault;
    
    public function setUp()
    {
        parent::setUp();
        $container = $this->getApplicationServiceLocator();
        $this->vault     = $container->get(VaultClient::class) ;
    }
    
    public function testVaultCanBeInstantiatedDirectly()
    {
        $vault = new VaultClient(['vault_address' => 'whatever']);
        $this->assertTrue(is_object($vault));
        $this->assertInstanceOf( VaultClient::class, $vault);
    }
    
    public function testVaultCanBeInstantiatedViaServiceManager()
    {
        $container = $this->getApplicationServiceLocator();        
        $vault = $container->get(VaultClient::class);
        $this->assertTrue(is_object($vault));
        $this->assertInstanceOf(VaultClient::class, $vault);
        return $vault;
    }
    
    /**
     *
     */
    public function testGetAddress()
    {
        $this->assertTrue(is_string($this->vault->getVaultAddress()));
    }
    
    /**
     * 
     */
    public function testUserAuthentication( )
    {
        $response = $this->vault->authenticateUser('username','password');
        // this is all for now. setting up a real vault instance
        // is a bit much for now
        $this->assertTrue(is_array($response));
    }
    
    /**
     * 
     * 
     */
    public function testAuthenticateTLSCert()
    {
        $vault = $this->vault;
        $vault->authenticateTLSCert();        
        //$this->assertTrue(is_object($response));
        //$data = json_decode($response);
        //$this->assertTrue(is_object($data));
        //$token = $data['auth']['client_token'];
        $token = $vault->getAuthToken();
        $this->assertTrue(is_string($token));
        return $vault;
    }
    
    /**
     * @depends testAuthenticateTLSCert
     */
    public function testAcquireCipherAccessToken(VaultClient $vault)
    {        
        
        $this->assertTrue(is_object($vault->requestCipherAccessToken()));        
        $this->assertTrue(is_string($vault->getAuthToken()));
        return $vault;
    }

    /**
     * 
     * //depends testAcquireCipherAccessToken
     * //param VaultClient $vault
     */
    public function testGetEncryptionKey()
    {
        $container = $this->getApplicationServiceLocator();
        $vault     = $container->get(VaultClient::class) ;
        
        $this->assertInstanceOf(VaultClient::class, $vault);
        $cipher = $vault->getEncryptionKey();
        $this->assertTrue(is_string($cipher));
        
        $string = "this is your string right here";
        $encrypted = $vault->encrypt($string);
        $this->assertTrue($string !== $encrypted);
        $decrypted = $vault->decrypt($encrypted);
        $this->assertTrue($string === $decrypted);
        
    }
}