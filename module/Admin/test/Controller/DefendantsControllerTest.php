<?php
namespace ApplicationTest\Controller;

use ApplicationTest\AbstractControllerTest;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;

use InterpretersOffice\Entity;
use Zend\Stdlib\Parameters;

use Zend\Dom;

class DefendantsControllerTest extends AbstractControllerTest
{

    public function setUp()
    {
        parent::setUp();
        FixtureManager::dataSetup([  new DataFixture\DefendantEventLoader(),]);
        $fixtureExecutor = FixtureManager::getFixtureExecutor();

        //$fixtureExecutor->execute(
        //    [  new DataFixture\DefendantEventLoader(),]
        //);

        //$this->login('susie', 'boink');
        //$this->reset(true);
    }

    public function testGetDefendants()
    {
        $this->login('david', 'boink');
        $this->reset(true);
        $this->assertTrue(true);

    }

}
