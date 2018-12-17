<?php /** module/Requests/test/Controller/RequestsAdminControllerTest.php */
 namespace ApplicationTest\Controller;

 use InterpretersOffice\Requests\Controller\RequestsIndexController;
 use ApplicationTest\AbstractControllerTest;

 use ApplicationTest\FixtureManager;
use ApplicationTest\FakeAuth;
 use ApplicationTest\DataFixture;
 use Zend\Stdlib\Parameters;

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
         $db->exec('DELETE FROM requests');
         $db->exec('DELETE FROM events');
         $db->exec('DELETE FROM defendants_requests');
         $db->exec('DELETE FROM defendants_events');
         // $result = $em->createQuery(
         //     'SELECT r FROM InterpretersOffice\Requests\Entity\Request r
         //        WHERE r.event IS NOT NULL'
         // )->getResult();
         // if (count($result)) {
         //     foreach ($result as $object) {
         //         $event = $object->getEvent();
         //         $em->remove($event);
         //         $em->remove($object);
         //     }
         //     $em->flush();
         // }
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

     public function testRequestAdminPageIsAccessible()
     {
         $this->dispatch('/admin/requests');
         $this->assertActionName('index');
         $this->assertResponseStatusCode(200);
     }



 }
