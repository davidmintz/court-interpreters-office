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
use Zend\Stdlib\Parameters;

use InterpretersOffice\Requests\Entity\Request;

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
                new DataFixture\DefendantLoader(),
                new DataFixture\LocationLoader(),
                new DataFixture\JudgeLoader(),
                new DataFixture\LanguageLoader(),
                new DataFixture\EventTypeLoader(),
                new DataFixture\InterpreterLoader(),
                new DataFixture\UserLoader(),
            ]
        );
        $container = $this->getApplicationServiceLocator();
        $em = $container->get("entity-manager");
        // $listener = $container->get('InterpretersOffice\Entity\Listener\UpdateListener');
        $resolver = $em->getConfiguration()->getEntityListenerResolver();
        $entityListener = $container->get('InterpretersOffice\Requests\Entity\Listener\RequestEntityListener');
        $entityListener->setLogger($container->get('log'));
        $resolver->register($entityListener);


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


}
