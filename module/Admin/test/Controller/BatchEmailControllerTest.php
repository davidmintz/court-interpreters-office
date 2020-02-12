<?php

namespace ApplicationTest\Controller;

use ApplicationTest\AbstractControllerTest;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;


class BatchEmailControllerTest extends AbstractControllerTest
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

    public function testRouteToEmailIndex()
    {
        $this->login('admin', 'boink');
        $this->reset(true);
        $this->dispatch('/admin/email');
        $this->assertResponseStatusCode(200);
    }

    public function testManagerAccessToBatchEmail()
    {
        $this->login('susie', 'boink');
        $this->reset(true);
        $this->dispatch('/admin/email');
        $this->assertResponseStatusCode(200);
    }

}
