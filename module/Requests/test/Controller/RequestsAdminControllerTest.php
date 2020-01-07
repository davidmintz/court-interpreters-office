<?php /** module/Requests/test/Controller/RequestsAdminControllerTest.php */
namespace ApplicationTest\Controller;

use InterpretersOffice\Requests\Controller\RequestsIndexController;
use ApplicationTest\AbstractControllerTest;
use ApplicationTest\FixtureManager;
//use ApplicationTest\FakeAuth;
use ApplicationTest\DataFixture;
use Laminas\Stdlib\Parameters;

use InterpretersOffice\Requests\Entity\Request;

use InterpretersOffice\Requests\Entity\Listener\RequestEntityListener;

/**
 * test for Request module Admin controller
 */
 class RequestsAdminControllerTest extends AbstractControllerTest
 {
     public function tearDown()
     {
         $em = FixtureManager::getEntityManager();
         $db = $em->getConnection();
         $db->exec('DELETE FROM defendants_requests');
         $db->exec('DELETE FROM defendants_events');
         $db->exec('DELETE FROM requests');
         $db->exec('DELETE FROM events');

     }

     public function setUp()
     {
         parent::setUp();
         FixtureManager::dataSetup();
         $fixtureExecutor = FixtureManager::getFixtureExecutor();
         $fixtureExecutor->execute([new DataFixture\RequestLoader],true);
         $this->login('david', 'boink');
         $this->reset(true);
     }

     public function testRequestAdminPageDisplaysPendingRequest()
     {
         $this->dispatch('/admin/requests');
         $this->assertActionName('index');
         $this->assertResponseStatusCode(200);
         $this->assertQuery("tbody > tr.request");
     }

     public function testAddRequestToSchedule()
     {
         // find a pending request
          $em = FixtureManager::getEntityManager();
          $id = $em->createQuery('SELECT r.id FROM InterpretersOffice\Requests\Entity\Request r WHERE r.pending = true')
            ->getSingleScalarResult();
          $url = "/admin/requests/schedule/$id";
          $this->login('david', 'boink');
          $this->reset(true);
          $this->getRequest()->setMethod('POST')
            ->setPost(
             new Parameters(['csrf'=>(new \Laminas\Validator\Csrf('csrf'))->getHash()])
            );
          $this->dispatch($url);
          $response = $this->getResponse()->getBody();
          $this->assertResponseStatusCode(200);
          $this->assertJson($response);
          $data = json_decode($response);
          $this->assertEquals('success',$data->status,'expected "success", response status was: '.$data->status);

     }
 }
