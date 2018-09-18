<?php
/**
 * module/Requests/test/Controller/RequestsIndexControllerTest.php
 *
 */

namespace ApplicationTest\Controller;

use InterpretersOffice\Requests\Controller\RequestsIndexController;
use ApplicationTest\AbstractControllerTest;

use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;

/**
 * unit test for InterpretersOffice\Requests module's main controller
 */
class RequestsIndexControllerTest extends AbstractControllerTest
{
    public function setUp()
    {
        parent::setUp();
        $fixtureExecutor = FixtureManager::getFixtureExecutor();
        $fixtureExecutor->execute(
            [
                new DataFixture\HatLoader(),
                //new DataFixture\HatLoader(),
                new DataFixture\LocationLoader(),
                new DataFixture\JudgeLoader(),
                new DataFixture\LanguageLoader(),
                new DataFixture\InterpreterLoader(),
                new DataFixture\UserLoader(),
            ]
        );
    }
    public function testIndexCannotBeAccessedWithoutLogin()
    {
        $this->dispatch('/requests');
        $this->assertRedirect();
    }

    public function testLoginSanity()
    {
        $this->login('jane_zorkendoofer@nysd.uscourts.gov','gack!');
        $this->reset(true);
        $this->dispatch('/requests');
        $this->assertResponseStatusCode(200);
    }

    public function testLoadCreatePage()
    {
        $this->login('jane_zorkendoofer@nysd.uscourts.gov','gack!');
        $this->reset(true);
        $this->dispatch('/requests/create');
        $this->assertResponseStatusCode(200);
        $this->assertQuery("form");

    }

}
