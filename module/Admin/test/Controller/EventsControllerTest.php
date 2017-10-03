<?php
namespace ApplicationTest\Controller;

use ApplicationTest\AbstractControllerTest;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;

class EventControllerTest extends AbstractControllerTest
{
    public function setUp()
    {
        parent::setUp();
        $fixtureExecutor = FixtureManager::getFixtureExecutor();
        FixtureManager::dataSetup();

        $this->login('david', 'boink');
        $this->reset(true);
    }
    
    public function testLoadEventInsertForm()
    {
        $this->dispatch('/admin/schedule/add');
        $this->assertResponseStatusCode(200);
        $this->assertQueryCount('form#event-form', 1);
        $this->assertQueryCount('#date',1);
        $this->assertQueryCount('#time',1);
        $this->assertQueryCount('#event-type',1);
        /** to be continued */
        
    }
        
}
