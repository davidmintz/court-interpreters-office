<?php /** module/Requests/test/Controller/RequestsAdminControllerTest.php */
 namespace ApplicationTest\Controller;

 use InterpretersOffice\Requests\Controller\RequestsIndexController;
 use ApplicationTest\AbstractControllerTest;

 use ApplicationTest\FixtureManager;
 use ApplicationTest\DataFixture;
 use Zend\Stdlib\Parameters;

 use InterpretersOffice\Requests\Entity\Request;

/**
 * test for Request module Admin controller
 */
 class RequestsAdminControllerTest extends AbstractControllerTest
 {

     public function setUp()
     {
         parent::setUp();
         FixtureManager::dataSetup();

         $this->login('david', 'boink');
         $this->reset(true);
     }

     public function testRequestAdminPageIsAccessible()
     {

         $this->dispatch('/admin/requests');
         $this->assertResponseStatusCode(200);
     }

 }
