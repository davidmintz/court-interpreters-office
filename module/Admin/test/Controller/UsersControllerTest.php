<?php
/**
 * module/Admin/test/Controller/UsersControllerTest.php
 */

namespace ApplicationTest\Controller;

use ApplicationTest\AbstractControllerTest;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;
use Laminas\Stdlib\Parameters;
use Laminas\Dom\Query;
use InterpretersOffice\Entity;

class UsersControllerTest extends AbstractControllerTest
{


    public function setUp()
    {
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
        $this->login('staffie', 'boink');
        $this->reset(true);
        // sanity test: make sure user is logged in
        $this->dispatch('/admin');
        $this->assertNotRedirect();

        $this->assertControllerClass('IndexController');
        $this->dispatch('/admin/users/add');
        //echo $this->getResponse()->getBody(); return;
        $this->assertResponseStatusCode(303);
        $this->assertNotControllerName('UsersController');
        $this->assertRedirect();
        $this->dispatch('/admin/users/add');
        $this->assertNotControllerName('UsersController');
        $this->assertRedirect();
        $this->assertResponseStatusCode(303);
    }

    public function testManagerCannotEditAdministratorAccount()
    {
        $em = FixtureManager::getEntityManager();
        $admin_user_id = $em->getRepository(Entity\User::class)
                ->findOneBy(['username' => 'admin'])
                ->getId();
        $this->login('staffie', 'boink');
        $this->reset(true);
        $url = "/admin/users/edit/$admin_user_id";
        $this->dispatch($url);
        $this->assertRedirect();
    }

    public function testAddUser()
    {
        $this->login('susie', 'boink');
        $this->reset(true);
        $this->dispatch('/admin/users/add');
        $this->assertResponseStatusCode(200);

    }
}
