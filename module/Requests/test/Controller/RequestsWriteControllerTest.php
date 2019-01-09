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
use Zend\Dom\Document\Query;
use Zend\Dom\Document;
use InterpretersOffice\Requests\Entity\Request;

/**
 * unit test for InterpretersOffice\Requests module's main controller
 */
class RequestsWriteControllerTest extends AbstractControllerTest
{
    public function setUp()
    {
        parent::setUp();
        $container = $this->getApplicationServiceLocator();
        $eventManager = $container->get('SharedEventManager');

        $eventManager->attach(Listener\UpdateListener::class,'*',function($e) use ($container) {
            $container->get('log')->debug(
                "SHIT HAS BEEN TRIGGERED! {$e->getName()} is the event, calling ScheduleUpdateManager"
            );
            /** @var ScheduleUpdateManager $updateManager */
            $updateManager = $container->get(ScheduleUpdateManager::class);
            $updateManager->onUpdateRequest($e);

        });
        // $container = $this->getApplicationServiceLocator();
        $em = FixtureManager::getEntityManager();//$container->get("entity-manager");
        // $listener = $container->get('InterpretersOffice\Entity\Listener\UpdateListener');
        $resolver = $em->getConfiguration()->getEntityListenerResolver();
        $entityListener = $container->get('InterpretersOffice\Requests\Entity\Listener\RequestEntityListener');
        $entityListener->setLogger($container->get('log'));
        $resolver->register($entityListener);
        $update_listener = $container->get('InterpretersOffice\Entity\Listener\UpdateListener');
        $resolver->register($update_listener);
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
                new DataFixture\RequestLoader(),
            ]
        );



    }

    public function tearDown()
    {
        $em = FixtureManager::getEntityManager();
        $result = $em->createQuery(
            'SELECT r FROM InterpretersOffice\Requests\Entity\Request r WHERE r.event IS NOT NULL'
        )->getResult();
        if (count($result)) {
            foreach ($result as $object) {
                $event = $object->getEvent();
                $em->remove($event);
                $em->remove($object);
            }
            $em->flush();
        }

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
    public function testLoadUpdateForm(Request $entity)
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
        $date = $entity->getDate()->format('m/d/Y');
        $this->assertQuery("input#date[value='$date']");

        return $entity;

    }
    /**
     * tests post method to update
     * @depends testCreate
     * @param  Request $entity [description]
     * @return [type]          [description]
     */
    public function testPostUpdate(Request $entity)
    {
        $date_before = $entity->getDate()->format('m/d/Y');
        // add a week to the date
        $new_date = (new \DateTime("$date_before +1 week"))->format('m/d/Y');

        $data = $this->getDummyRequest();
        $data['date'] = $new_date;
        $data['id']   = $entity->getId();
        $this->login('jane_zorkendoofer@nysd.uscourts.gov','gack!');
        $this->reset(true);
        $url = "/requests/update/{$entity->getId()}";
        $token = $this->getCsrfToken($url);
        $this->assertTrue(is_object($entity));
        //return;
        $post = ['csrf' => $token,'request'=> $data];
        $this->getRequest()->getHeaders()
            ->addHeaderLine('X-Requested-With','XMLHttpRequest');
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($post)
        );
        $this->dispatch($url);
        $this->assertResponseStatusCode(200);
        $em = $this->getApplicationServiceLocator()->get('entity-manager');
        $reloaded_entity = $em->find(Request::class,$data['id']);
        $dateObj = $reloaded_entity->getDate();
        // date should be $new_date
        $this->assertEquals($new_date, $dateObj->format('m/d/Y'));
    }

    /**
     * testClerkCannotUpdateRequestBelongingToAnotherJudge
     * @depends testCreate
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

    public function testLoadRequestThatIsAlreadyScheduled()
    {
        $em = $this->getApplicationServiceLocator()->get('entity-manager');
        $request = $em->createQuery(
            'SELECT r FROM InterpretersOffice\Requests\Entity\Request r WHERE r.event IS NOT NULL')
            ->getOneOrNullResult();
        $this->assertTrue(is_object($request));
        $this->login('john_somebody@nysd.uscourts.gov','gack!');
        $this->reset(true);
        //echo "\nRequest is for Judge: ",$request->getJudge()->getLastName(),"\n";
        //$shit = $em->createQuery('')
        $url = "/requests/update/{$request->getId()}";
        $this->dispatch($url);
        $this->assertResponseStatusCode(200);
        $this->assertQuery("form");

        $this->assertQueryContentRegex("ul#defendants > li", '/Fulano Mengano/');
        $date = $request->getDate()->format('m/d/Y');
        $this->assertQuery("input#date[value='$date']");
        $time = $request->getTime()->format('H:i a');
        $this->assertQuery("input#time[value='$time']");

        $this->assertQuery("select#eventType option");
        $q = new \Zend\Dom\Query($this->getResponse()->getBody());
        $options = $q->execute("select#eventType option");
        $this->assertTrue(count($options) > 5);
        $selected = null;
        foreach($options as $opt) {
            /** @var \DOMElement $opt */
            if ($opt->getAttribute('selected')) {
                $selected = $opt;
                break;
            }
        }
        $this->assertNotNull($selected);
        $this->assertEquals($selected->textContent, (string)$request->getEventType());

        $courtroom = $request->getJudge()->getDefaultLocation()->getName();

        $options = $q->execute('#location option');
        $selected = null;
        foreach($options as $opt) {
            /** @var \DOMElement $opt */
            if ($opt->getAttribute('selected')) {
                $selected = $opt;
                break;
            }
        }
        $this->assertNotNull($selected);
        $this->assertEquals($selected->textContent, $courtroom);

        $this->assertQuery("input#csrf");

        return $request;

    }

    /**
     * @return [type] [description]
     */
    public function testPostUpdateToScheduledRequest()
    {
        //$this->assertTrue(is_object($request));
        $em = $this->getApplicationServiceLocator()->get('entity-manager');
        $request = $em->createQuery(
            'SELECT r FROM InterpretersOffice\Requests\Entity\Request r WHERE r.event IS NOT NULL')
            ->getOneOrNullResult();
        $this->assertTrue(is_object($request));
        $this->login('john_somebody@nysd.uscourts.gov','gack!');
        $this->reset(true);
        $url = "/requests/update/{$request->getId()}";
        $token = $this->getCsrfToken($url);
        $deft_ids = [];
        foreach ($request->getDefendants() as $d) {
            $deft_ids[] = $d->getId();
        }
        //$time = $request->getTime()->format('H:i a');
        //echo "\ntime is $time";
        $post = [
            'request' => [
                'id' => $request->getId(),
                'date' => $request->getDate()->format('m/d/Y'),
                'time' =>'3:00 pm', // later in the date
                'docket' => $request->getDocket(),
                'language' => $request->getLanguage()->getId(),
                'judge' => $request->getJudge()->getId(),
                'location' => $request->getLocation()->getId(),
                'defendants' => $deft_ids,
                'comments' => 'Time and the bell have buried the day.',
                'eventType' => $request->getEventType()->getId(),
            ],
            'csrf' => $token,
        ];

        $this->getRequest()->getHeaders()
            ->addHeaderLine('X-Requested-With','XMLHttpRequest');
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($post)
        );
        $this->dispatch($url);
        $this->assertResponseStatusCode(200);
        echo "\n";
        $this->dumpResponse();
    }

    public function testViewRequestThatIsScheduled()
    {

        $em = $this->getApplicationServiceLocator()->get('entity-manager');
        $request = $em->createQuery(
            'SELECT r FROM InterpretersOffice\Requests\Entity\Request r WHERE r.event IS NOT NULL')
            ->getOneOrNullResult();
        $this->assertTrue(is_object($request));
        $this->login('john_somebody@nysd.uscourts.gov','gack!');
        $this->reset(true);
        //echo "\nRequest is for Judge: ",$request->getJudge()->getLastName(),"\n";
        //$shit = $em->createQuery('')
        $url = "/requests/view/{$request->getId()}";
        /** @var \Doctrine\DBAL\Connection $db */
        $db = $em->getConnection();
        $shit = $db->executeQuery('SELECT * FROM requests WHERE id = '.$request->getId());
        echo get_class($shit);;
        $data = $shit->fetch();
        print_r($data);
        $this->dispatch($url);
         echo "\n$url\n";
        $this->assertResponseStatusCode(200);

        $this->dumpResponse();
    }
    /*        //$csrf = $q->execute("input#csrf")->current();

            //echo "\n date and time are $date and $time, csrf {$csrf->getAttribute('value')}\n";
            $deft_ids = [];
            foreach ($request->getDefendants() as $d) {
                $deft_ids[] = $d->getId();
            }
            $post = [
                'request' => [
                    'id' => $request->getId(),
                    'date' => $date,
                    'time' => $time,
                    'docket' => $request->getDocket(),
                    'language' => $request->getLanguage()->getId(),
                    'judge' => $request->getJudge()->getId(),
                    'location' => $request->getLocation()->getId(),
                    'defendants' => $deft_ids,
                    'comments' => $request->getComments(),
                ],
                //'csrf' => $csrf->textContent
            ];

            $this->login('john_somebody@nysd.uscourts.gov','gack!');
            $this->reset(true);
            $url = "/requests/update/{$request->getId()}";
            $token = $this->getCsrfToken($url);
            $post['csrf'] = $token;
            $this->getRequest()->getHeaders()
                ->addHeaderLine('X-Requested-With','XMLHttpRequest');
            $this->getRequest()->setMethod('POST')->setPost(
                new Parameters($post)
            );
            $this->dispatch($url);

            $this->assertResponseStatusCode(200);

            // $this->getRequest()->getHeaders()
            //     ->addHeaderLine('X-Requested-With','XMLHttpRequest');
            // $this->getRequest()->setMethod('POST')->setPost(
            //     new Parameters($post)
            // );
            // $this->dispatch($url);
            // $this->assertResponseStatusCode(200);
            //
            // $this->dumpResponse();

            return $request;
                */

}
