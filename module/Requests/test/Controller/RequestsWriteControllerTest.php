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
class RequestsWriteControllerTest extends AbstractControllerTest
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

    public function getDummyRequest()
    {

        $em = FixtureManager::getEntityManager();


        $dql = "SELECT j.id FROM InterpretersOffice\Entity\User u JOIN u.judges j JOIN u.person p
        WHERE p.email = 'jane_zorkendoofer@nysd.uscourts.gov'";

        $judge = $em->createQuery($dql)->getResult()[0]['id'];
        $spanish = $em->createQuery(
            "SELECT l.id FROM InterpretersOffice\Entity\Language l
                WHERE l.name = 'Spanish'"
            )->getSingleScalarResult();
        $conference = $em->createQuery(
            "SELECT t.id FROM InterpretersOffice\Entity\EventType t
                WHERE t.name = 'conference'"
            )->getSingleScalarResult();
        $location = $em->find('InterpretersOffice\Entity\Judge',$judge)
            ->getDefaultLocation()->getId();
        $defendant_id = $em->createQuery('SELECT d.id FROM  InterpretersOffice\Entity\Defendant d WHERE d.surnames = :surnames')
            ->setParameters(['surnames'=>'Fulano Mengano'])
            ->getSingleScalarResult();

        return [
                'judge' => $judge,
                'docket' => '2018-CR-1234',
                'language' => $spanish,
                'eventType' => $conference,
                'location'  => $location,
                'date' => (new \DateTime('next Tuesday +1 week'))->format('m/d/Y'),
                'time' => (new \DateTime("today 10:00 am"))->format('g:i a'),
                'comments' => 'boink gack babble babble',
                'defendants' =>  [ $defendant_id ],
                'id' => '',
            ];
    }

    public function testLoadCreatePage()
    {
        $this->login('jane_zorkendoofer@nysd.uscourts.gov','gack!');
        $this->reset(true);
        $this->dispatch('/requests/create');
        $this->assertResponseStatusCode(200);
        $this->assertQuery("form");
        $this->assertQuery("#date");
        $this->assertQuery("#time");
        $this->assertQuery("#defendants");
        //this->dumpResponse();
        //$this->assertQuery("ul#defendants > li");


    }
    /**
     * tests create action
     *
     * @return Request
     */
    public function testCreate()
    {

        $em = $this->getApplicationServiceLocator()->get('entity-manager');
        $log = $this->getApplicationServiceLocator()->get('log');

        $before = $em->createQuery('SELECT COUNT(r.id) FROM InterpretersOffice\Requests\Entity\Request r')
            ->getSingleScalarResult();
        $data = $this->getDummyRequest();
        $this->login('jane_zorkendoofer@nysd.uscourts.gov','gack!');
        $this->reset(true);
        $token = $this->getCsrfToken('/requests/create');
        $post = ['csrf' => $token,'request'=> $data];
        $this->reset(true);
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($post)
        );
        $this->dispatch('/requests/create');

        $this->assertResponseStatusCode(200);
        $after = $em->createQuery('SELECT COUNT(r.id) FROM InterpretersOffice\Requests\Entity\Request r')
           ->getSingleScalarResult();

        $this->assertTrue($after == $before + 1);

        $entity = $em->createQuery('SELECT r FROM InterpretersOffice\Requests\Entity\Request r ORDER BY r.id DESC')
            ->setMaxResults(1)
            ->getOneOrNullResult();

        $this->assertTrue(is_object($entity));
        $this->assertTrue($entity instanceof Request);

        $person = $entity->getSubmitter();
        $this->assertEquals($person->getEmail(),'jane_zorkendoofer@nysd.uscourts.gov');

        return $entity;
    }

    /**
     * @depends testCreate
     * @param  Request $entity
     * @return Request
     */
    public function testUpdate(Request $entity)
    {
        $this->assertTrue($entity instanceof Request);
        $em = $this->getApplicationServiceLocator()->get('entity-manager');
        $this->login('jane_zorkendoofer@nysd.uscourts.gov','gack!');
        $this->reset(true);
        $url = "/requests/update/{$entity->getId()}";
        $token = $this->getCsrfToken($url);
        $this->dispatch($url);
        $this->assertResponseStatusCode(200);
        $this->assertActionName('update');
        $this->assertQuery("form");
        $this->assertQuery("#date");
        $this->assertQuery("#time");
        $this->assertQuery("#judge");
        $this->assertQuery("#docket");
        $this->assertQuery("#language");
        $this->assertQuery("#defendants");
        $this->assertQuery("ul#defendants > li");
        $this->assertQueryCount("ul#defendants > li",1);
        $this->assertQueryContentRegex("ul#defendants > li", '/Fulano Mengano/');
        // to be continued
        return $entity;

    }

    /**
     * testClerkCannotUpdateRequestBelongingToAnotherJudge
     * @depends testUpdate
     *
     * @param  Request $entity
     * @return Request $entity
     */
    public function testClerkCannotUpdateRequestBelongingToAnotherJudge(Request $entity)
    {
        $this->login('john_somebody@nysd.uscourts.gov','gack!');
        $this->reset(true);
        $url = "/requests/update/{$entity->getId()}";
        $this->dispatch($url);
        $this->assertResponseStatusCode(403);
        $this->assertNotQuery("form");
        $this->assertQuery("div.alert");
        $this->assertQueryContentRegex("div.alert",'/not authorized/');

        echo "\nno shit?\n";

        return $entity;
    }

    /**
     * tests users can update each others' requests if the requests have a common judge
     * @todo and are in-court
     * @depends testClerkCannotUpdateRequestBelongingToAnotherJudge
     *
     * @param  Request $entity
     * @return Request $entity
     */
    public function testClerkCanUpdateOthersRequestBelongingToACommonJudge(Request $entity)
    {
        $this->login('bill_dooflicker@nysd.uscourts.gov','gack!');
        $this->reset(true);
        $url = "/requests/update/{$entity->getId()}";
        $this->dispatch($url);
        $this->assertResponseStatusCode(200);
        $this->assertQuery("form");

        return $entity;
    }
}
