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

    public function testAccessToAdminConfig()
    {
        $this->login('susie', 'boink');
        $this->reset(true);
        $this->dispatch('/admin/configuration');
        $this->assertNotResponseStatusCode(404);
        // they should get redirected
        $this->assertResponseStatusCode(303);
    }

}
