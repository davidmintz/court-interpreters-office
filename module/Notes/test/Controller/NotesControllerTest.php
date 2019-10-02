<?php

namespace ApplicationTest\Controller;

use InterpretersOffice\Requests\Controller\RequestsIndexController;
use ApplicationTest\AbstractControllerTest;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;
use Zend\Stdlib\Parameters;

class NotesControllerTest extends AbstractControllerTest
{
    public function setUp()
    {
        parent::setUp();
        $fixtureExecutor = FixtureManager::getFixtureExecutor();
        $fixtureExecutor->execute(
            [
                // new DataFixture\DefendantLoader(),
                // new DataFixture\EventTypeLoader(),
                new DataFixture\HatLoader(),
                new DataFixture\LocationLoader(),
                new DataFixture\JudgeLoader(),
                new DataFixture\LanguageLoader(),
                new DataFixture\InterpreterLoader(),
                new DataFixture\UserLoader(),
            ]
        );
    }

    public function testGetMethodWorksIfUserIsLoggedIn()
    {
        $this->login('david','boink');
        $this->reset(true);
        $this->dispatch('/admin/notes/motd/id/3');
        $this->assertResponseStatusCode(200);
    }

    public function testGetMethodFailsIfUserIsNotLoggedIn()
    {
        $this->dispatch('/admin/notes/motd/id/3');
        $this->assertNotResponseStatusCode(200);
    }

    public function testGetByDateMethodWorks()
    {
        $this->login('david','boink');
        $this->reset(true);
        $this->dispatch('/admin/notes/date/2019-09-12/motd');
        $this->assertResponseStatusCode(200);
    }

}
