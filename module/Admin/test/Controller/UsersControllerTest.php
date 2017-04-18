<?php
/**
 * module/Admin/test/Controller/UsersControllerTest.php
 */

namespace ApplicationTest\Controller;

use ApplicationTest\AbstractControllerTest;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;
use Zend\Stdlib\Parameters;
use Zend\Dom\Query;
use InterpretersOffice\Entity;

class UsersControllerTest extends AbstractControllerTest
{
    

    public function setUp() {
        parent::setUp();
        $fixtureExecutor = FixtureManager::getFixtureExecutor();

        $fixtureExecutor->execute(
                [
                    new DataFixture\MinimalUserLoader(),
                   
                ]
        );

        //$this->login('susie', 'boink');
    }
    
    public function testStaffCannotCreateNewUsers()
    {
        $this->login('staffie','boink');
        $this->reset(true);
        // sanity test: make sure user is logged in
        $this->dispatch('/admin');
        $this->assertNotRedirect();
        
        $this->assertControllerClass('AdminIndexController');
        $this->dispatch('/admin/users');
        $this->assertResponseStatusCode(303);
        $this->assertNotControllerName('UsersController');
        $this->assertRedirect();
        $this->dispatch('/admin/users/add');
        $this->assertNotControllerName('UsersController');
        $this->assertRedirect();
        $this->assertResponseStatusCode(303);        
    }
}