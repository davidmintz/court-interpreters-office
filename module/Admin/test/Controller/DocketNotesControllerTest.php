<?php

namespace ApplicationTest\Controller;

use ApplicationTest\AbstractControllerTest;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;


class DocketNotesControllerTest extends AbstractControllerTest
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

    public function testRouteToAnnotations()
    {
        $this->login('admin', 'boink');
        $this->reset(true);
        $this->dispatch('/admin/docket-annotations');
        $this->assertResponseStatusCode(200);

        $this->dispatch('/admin/docket-annotations/2018-CR-0345');
        $this->assertResponseStatusCode(200);

        $this->dispatch('/admin/docket-annotations/add');
        $this->assertResponseStatusCode(200);

        $this->dispatch('/admin/docket-annotations/2018-CR-0345/add');
        $this->assertResponseStatusCode(200);

        $this->dispatch('/admin/docket-annotations/random-shit-0345/add');
        $this->assertResponseStatusCode(404);

    }

    public function testManagerAccessToAnnotationsIndex()
    {
        $this->login('susie', 'boink');
        $this->reset(true);
        $this->dispatch('/admin/docket-annotations');
        $this->assertResponseStatusCode(200);
    }

}
