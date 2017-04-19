<?php
namespace SDNY\Vault\Test;

use ApplicationTest\AbstractControllerTest;

use SDNY\Vault\Service\Vault;

class VaultInitializationTest extends AbstractControllerTest 
{
    public function setUp()
    {
        parent::setUp();
    }
    
    public function testVaultCanBeInstantiatedDirectly()
    {
        $vault = new Vault([]);
        $this->assertTrue(is_object($vault));
        $this->assertInstanceOf( Vault::class, $vault);
    }
    
    public function testVaultCanBeInstantiatedViaServiceManager()
    {
        $container = $this->getApplicationServiceLocator();        
        $vault = $container->get(Vault::class);
        $this->assertTrue(is_object($vault));
        $this->assertInstanceOf(Vault::class, $vault);
    }
}