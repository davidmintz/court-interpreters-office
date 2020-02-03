<?php
/**
 * module/Requests/test/Controller/ScheduleUpdateManagerTest.php
 *
 */

namespace ApplicationTest\Controller;

use ApplicationTest\AbstractControllerTest;
use ApplicationTest\FixtureManager;
use ApplicationTest\FakeAuth;
use ApplicationTest\DataFixture;

use Laminas\Dom;
use Laminas\Stdlib\Parameters;

use InterpretersOffice\Entity as IOEntity;

/**
 * tests for ScheduleUpdateManager
 */
class ScheduleUpdateManagerTest extends AbstractControllerTest
{


    public function setUp()
    {
        parent::setUp();
        $container = $this->getApplicationServiceLocator();
        $em = FixtureManager::getEntityManager();
        //$pdo = $em->getConnection();
        //$em = $this->em;//FixtureManager::getEntityManager();
        $pdo = $em->getConnection()->getWrappedConnection();
        //$pdo->query('DELETE FROM events');
        $pdo->query('DELETE FROM requests WHERE event_id IS NOT NULL');

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
        //$container->get("entity-manager");
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
        $request_repo = $em->getRepository('InterpretersOffice\Requests\Entity\Request');
        $result = $em->createQuery(
            'SELECT r FROM InterpretersOffice\Requests\Entity\Request r
            WHERE r.event IS NOT NULL'
        )->getResult();
        // debug
        //printf("\nfound %d requests on schedule in %s\n",count($result),__FUNCTION__);
        foreach($result as $r) {
            $event = $r->getEvent();
            $interpreters = $event->getInterpreters();
            //printf("\nfound %d interpreters on scheduled request in %s\n",count($interpreters),__FUNCTION__);
            //printf("\nlanguage is %s in %s\n",$event->getLanguage(),__FUNCTION__);
            if (! count($interpreters)) {
                $i = $em->createQuery('SELECT i FROM InterpretersOffice\Entity\Interpreter i WHERE i.lastname = :lastname')
                    ->setParameters(['lastname'=>'Somebody'])
                    ->getOneOrNullResult();
                $u = $em->createQuery('SELECT u FROM InterpretersOffice\Entity\User u WHERE u.username = :username')
                    ->setParameters(['username'=>'david'])
                    ->getOneOrNullResult();
                $ie = new \InterpretersOffice\Entity\InterpreterEvent($i,$event);
                $ie->setCreatedBy($u);
                $event->addInterpreterEvents(new \Doctrine\Common\Collections\ArrayCollection([$ie]));
            }
            $em->flush();

        }
        $this->em = $em;


    }
    private $em;

    private $autocancellation_notice = './data/email-autocancellation.html';

    private $update_notice = './data/event-update-notice.html';

    public function tearDown()
    {
        $em = FixtureManager::getEntityManager();
        $pdo = $em->getConnection()->getWrappedConnection();
        //$pdo->query('DELETE FROM events');
        $pdo->query('DELETE FROM requests WHERE event_id IS NOT NULL');

        // $result = $em->createQuery(
        //     'SELECT r FROM InterpretersOffice\Requests\Entity\Request r
        //     WHERE r.event IS NOT NULL'
        // )->getResult();
        // if (count($result)) {
        //     foreach ($result as $object) {
        //         $event = $object->getEvent();
        //         $em->remove($event);
        //         $em->remove($object);
        //     }
        //     $em->flush();
        // }
        if (file_exists($this->autocancellation_notice)) {
            unlink($this->autocancellation_notice);
        }
        if (file_exists($this->update_notice)) {
            unlink($this->update_notice);
        }
    }

    public function testDataSetupSanity()
    {

        $em = $this->em;
        $request_repo = $em->getRepository('InterpretersOffice\Requests\Entity\Request');
        $result = $em->createQuery(
            'SELECT r FROM InterpretersOffice\Requests\Entity\Request r
            WHERE r.event IS NOT NULL'
        )->getResult();
        $this->assertGreaterThan(0,count($result));
        $request = $result[0];
        $event = $request->getEvent();
        $this->assertInstanceOf('InterpretersOffice\Entity\Event', $event);
        $interpreter = $event->getInterpreters()[0];
        $this->assertInstanceOf('InterpretersOffice\Entity\Interpreter', $interpreter);

    }

    public function testOnRequestCancellationScheduledEventIsDeletedAutomatically()
    {
        $this->login('john','gack!');
        $this->reset(true);
        // get our request id
        $result = $this->em->createQuery("SELECT r FROM InterpretersOffice\Requests\Entity\Request r
        JOIN r.submitter p
        JOIN r.language l
        JOIN InterpretersOffice\Entity\User u
        WITH u.person = p WHERE u.username = :username AND l.name = :language")
        ->setParameters(['username'=>'john','language'=>"Spanish"])->getResult();
        $this->assertTrue(count($result) == 1);
        $request = $result[0];
        // sanity-check
        $event = $request->getEvent();
        $this->assertInstanceOf('InterpretersOffice\Entity\Event', $event);
        $event_id = $event->getId();
        // get a CSRF token
        $this->dispatch("/requests/list");
        $html = $this->getResponse()->getBody();
        $this->reset(true);
        $dom = new Dom\Query($html);
        $result = $dom->execute('#requests-table');
        $table = $result->current();
        $csrf = $table->getAttribute('data-csrf');
        $this->assertFalse(file_exists($this->autocancellation_notice));
        $this->getRequest()->setMethod('POST')
            ->setPost(new Parameters(['csrf'=>$csrf]));
        $this->dispatch('/requests/cancel/'.$request->getId());
        $data = $this->getResponse()->getBody();
        //$this->dumpResponse();//return;
        $response = json_decode($data);
        $this->assertEquals("success",$response->status);

        $event = $this->getApplicationServiceLocator()->get("entity-manager")
            ->find('InterpretersOffice\Entity\Event',$event_id);
        // $this->assertNull($event);
        $this->assertTrue($event->isDeleted());

        // make sure an email was generated (body dumped for test purposes)
        $this->assertTrue(file_exists($this->autocancellation_notice));

    }

    public function testNonspanishInterpretersAreNotifiedWhenAutomaticallyRemoved()
    {
        $result = $this->em->createQuery("SELECT r FROM InterpretersOffice\Requests\Entity\Request r
        JOIN r.submitter p
        JOIN r.language l
        JOIN InterpretersOffice\Entity\User u
        WITH u.person = p WHERE u.username = :username AND l.name = :language")
        ->setParameters(['username'=>'john','language'=>"Russian"])->getResult();

        $this->assertTrue(is_object($result[0]));
        $request = $result[0];
        // check our data setup
        $event = $request->getEvent();
        // sanity check
        $this->assertInstanceOf(IOEntity\Event::class,$event);
        $interpreters  = $event->getInterpreters();
        $this->assertTrue(count($interpreters) != 0);
        $russian_interpreter = $interpreters[0];
        $languages = $russian_interpreter->getLanguages();
        $this->assertEquals('Russian',(string)$languages[0]);

        $this->login('john','gack!');
        $this->reset(true);
        $id = $request->getId();
        $url = '/requests/update/'.$id;
        $csrf = $this->getCsrfToken($url);
        $new_date = new \DateTime(
            sprintf("%s + 1 weeks",$request->getDate()->format('Y-m-d'))
        );
        $post = [
            'csrf' => $csrf,
            'request' => [
                'date' => $new_date->format('m/d/Y'),
                'time' => $request->getTime()->format('g:i a'),
                'judge' => $request->getJudge()->getId(),
                'language' => $request->getLanguage()->getId(),
                'docket' => $request->getDocket(),
                'event_type' => $request->getEventType()->getId(),
                'defendants' => [
                     $request->getDefendants()[0]->getId()
                ],
                'comments' => $request->getComments(),
                'id' => $request->getId(),
            ],
        ];
        $this->reset(true);
        $this->getRequest()->setMethod('POST')
            ->setPost(new Parameters($post));
        $this->dispatch($url);
        $this->assertResponseStatusCode(200);
        $this->assertResponseHeaderRegex('content-type','|application/json|');
        //$this->dumpResponse();
        $response = json_decode($this->getResponse()->getBody());
        $this->assertEquals("success",$response->status);

        // printf("\n\$interpreters is a: %s\n",gettype($interpreters));
        // printf("\n\$interpreters has count: %s\n",count($interpreters));
        // printf("\n\$event language: %s\n",$event->getLanguage());
        // printf("\ninterpreter is: %s\n",$interpreters[0]->getFullname());
        // printf("\nrequest comments are: %s\n",$request->getComments());

    }

    public function testInterpretersAreNotififiedOnChangeTimeWithinAmPmBoundary()
    {
        $result = $this->em->createQuery("SELECT r FROM InterpretersOffice\Requests\Entity\Request r
        JOIN r.submitter p
        JOIN r.language l
        JOIN InterpretersOffice\Entity\User u
        WITH u.person = p WHERE u.username = :username AND l.name = :language")
        ->setParameters(['username'=>'john','language'=>"Spanish"])->getResult();

        $this->assertTrue(is_object($result[0]));
        $request = $result[0];
        // check our data setup
        $event = $request->getEvent();
        // sanity check
        $this->assertInstanceOf(IOEntity\Event::class,$event);
        $interpreters  = $event->getInterpreters();
        $this->assertTrue(count($interpreters) != 0);
        $russian_interpreter = $interpreters[0];
        $languages = $russian_interpreter->getLanguages();
        $this->assertEquals('Spanish',(string)$languages[0]);

        $this->login('john','gack!');
        $this->reset(true);
        $id = $request->getId();
        $url = '/requests/update/'.$id;
        $csrf = $this->getCsrfToken($url);
        $new_time = new \DateTime(
            sprintf("%s + 1 hours",$request->getTime()->format('g:i a'))
        );
        $post = [
            'csrf' => $csrf,
            'request' => [
                'date' => $request->getDate()->format('m/d/Y'),
                'time' => $new_time->format('g:i a'),
                'judge' => $request->getJudge()->getId(),
                'language' => $request->getLanguage()->getId(),
                'docket' => $request->getDocket(),
                'event_type' => $request->getEventType()->getId(),
                'defendants' => [
                     $request->getDefendants()[0]->getId()
                ],
                'comments' => 'just updated comments in '.__FUNCTION__,
                'id' => $request->getId(),
            ],
        ];
        $this->reset(true);
        $this->getRequest()->setMethod('POST')
            ->setPost(new Parameters($post));
        $this->dispatch($url);
        $this->assertResponseStatusCode(200);
        $this->assertResponseHeaderRegex('content-type','|application/json|');

        $this->assertTrue(file_exists($this->update_notice));

    }
    /**
     *
     *
     */
    function __testInterpretersGetEmail()
    {

    }
}
