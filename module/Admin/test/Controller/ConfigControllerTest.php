<?php

namespace ApplicationTest\Controller;

use ApplicationTest\AbstractControllerTest;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;


class ConfigControllerTest extends AbstractControllerTest
{
    public function setUp()
    {

        parent::setUp();
        $fixtureExecutor = FixtureManager::getFixtureExecutor();
        $fixtureExecutor->execute(
            [ new DataFixture\MinimalUserLoader(),
            ]
        );
    }

    public function testRouteToAdminConfig()
    {
        $this->login('admin', 'boink');
        $this->reset(true);
        $this->dispatch('/admin/configuration');
        $this->assertNotResponseStatusCode(404);
        $this->assertResponseStatusCode(200);
    }

    public function testManagerAccessToAdminConfig()
    {
        $this->login('susie', 'boink');
        $this->reset(true);
        $this->dispatch('/admin/configuration');
        $this->assertNotResponseStatusCode(303);
        // they should not get redirected
        $this->assertResponseStatusCode(200);
    }

    public function testManagerCanReadButNotUpdateConfigForm()
    {
        $this->login('susie', 'boink');
        $this->reset(true);
        $this->dispatch('/admin/configuration/forms');
        $this->assertResponseStatusCode(200);
        $token = $this->getCsrfToken('/admin/configuration/forms');
        $this->dispatch('/admin/configuration/forms/update','POST',
            [
                    // bla bla, whatever...
                    'csrf' => $token,
            ]
        );
        $this->assertNotResponseStatusCode(200);
        $this->assertResponseStatusCode(303);

    }

}
