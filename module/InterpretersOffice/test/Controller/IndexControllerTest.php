<?php
/**
 * unit test for InterpretersOffice module's main controller
 */

namespace ApplicationTest\Controller;

use InterpretersOffice\Controller\IndexController;
use ApplicationTest\AbstractControllerTest;

class IndexControllerTest extends AbstractControllerTest
{
    /*
    public function setUp()
    {
        parent::setUp();
    }
    */

    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch('/', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('interpretersoffice');
        $this->assertControllerName(IndexController::class); // as specified in router's controller name alias
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('home');

        //echo $this->getResponse()->getBody();
    }

    public function testIndexActionViewModelTemplateRenderedWithinLayout()
    {
        $this->dispatch('/', 'GET');
        $this->assertQuery('.container');
    }

    public function testInvalidRouteDoesNotCrash()
    {
        $this->dispatch('/invalid/route', 'GET');
        $this->assertResponseStatusCode(404);
    }
}
