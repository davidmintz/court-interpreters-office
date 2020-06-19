<?php
namespace ApplicationTest\Controller;

use ApplicationTest\AbstractControllerTest;
use ApplicationTest\FixtureManager;

use InterpretersOffice\Entity;
use Laminas\Stdlib\Parameters;

use Laminas\Dom;

use ApplicationTest\DummyDataTrait;

class EventsControllerTest extends AbstractControllerTest
{

    use DummyDataTrait;

    public function setUp()
    {
        parent::setUp();
        FixtureManager::dataSetup();

        $this->login('david', 'boink');
        $this->reset(true);
    }

    

    public function testLoadEventInsertForm()
    {
        $this->dispatch('/admin/schedule/add');
        $this->assertResponseStatusCode(200);
        $this->assertQueryCount('form#event-form', 1);
        $this->assertQueryCount('#date', 1);
        $this->assertQueryCount('#time', 1);
        $this->assertQueryCount('#event_type', 1);
        $this->assertQueryCount('#judge', 1);
        $this->assertQueryCount('#language', 1);
        $this->assertQueryCount('#docket', 1);
        $this->assertQueryCount('#location', 1);
        $this->assertQueryCount('#parent_location', 1);
        $this->assertQueryCount('#hat', 1);
        $this->assertQueryCount('#submission_date', 1);
        $this->assertQueryCount('#submission_time', 1);
        $this->assertQueryCount('#comments', 1);
        $this->assertQueryCount('#admin_comments', 1);
        $this->assertQueryCount('#anonymous_judge', 1);
        $this->assertQueryCount('#is_anonymous_judge', 1);
    }

    public function testAddInCourtEvent()
    {
        //$data = [];
        $em = FixtureManager::getEntityManager();

        $count_before = $em
          ->createQuery('SELECT COUNT(e.id) FROM InterpretersOffice\Entity\Event e')
          ->getSingleScalarResult();
        $event = $this->getDummyData();
        $this->login('david', 'boink');
        $this->reset(true);
        $token = $this->getCsrfToken('/admin/schedule/add');
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters([
                    'event' => $event,
                    'csrf' => $token,
                ])
        );
        $this->dispatch('/admin/schedule/add');
        $count_after = $em
          ->createQuery('SELECT COUNT(e.id) FROM InterpretersOffice\Entity\Event e')
          ->getSingleScalarResult();
        $this->assertEquals(
            1,
            $count_after - $count_before,
            "count $count_after was not incrememented by one (from $count_before)"
        );
        // pull it out and take a look.
        $q = 'SELECT MAX(e.id) FROM InterpretersOffice\Entity\Event e';
        $id = $em->createQuery($q)->getSingleScalarResult();
        $data = $em->getRepository(Entity\Event::class)->getView($id)['event'];
        $this->assertTrue(false !== strstr($data['judge'], 'Dinklesnort'));
        $this->assertTrue(false !== strstr($data['type'], 'conference'));
        $this->assertEquals($data['location'], '14B, 500 Pearl');
        $this->assertEquals($data['parent_location'], '500 Pearl');
        $this->assertEquals($data['created_by'], 'david');
        $this->assertEquals($data['language'], 'Spanish');
        $this->assertTrue(
            false !== strstr($data['submitter'], 'Zorkendoofer'),
            "string 'Zorkendoofer' not contained in string '{$data['submitter']}'"
        );
        $this->assertEquals($data['submitter_hat'], 'Law Clerk');
        $entity = $em->find(Entity\Event::class, $id);
        return $entity;
    }

    public function testBatchInsertInCourtEvent()
    {
        $em = FixtureManager::getEntityManager();

        $count_before = $em
          ->createQuery('SELECT COUNT(e.id) FROM InterpretersOffice\Entity\Event e')
          ->getSingleScalarResult();
        $event = $this->getDummyData();
        $event['dates'] = call_user_func(function(string $monday){
            $dates = [preg_replace('|(\d\d)/(\d\d)/(\d\d\d\d)|','$3-$1-$2',$monday)];
            $obj = new \DateTimeImmutable($monday);
            for ($i = 1; $i <= 4; $i++) {
                $interval = new \DateInterval("P${i}D");
                $dates[] = $obj->add($interval)->format("Y-m-d");
            }
            //print_r($dates);
            return $dates;
        },$event['date']);
        $trial = $em->getRepository(Entity\EventType::class)->findOneBy(['name'=>'trial']);
        $data['event_type'] = $trial->getId();
        $this->login('david', 'boink');
        $this->reset(true);
        $token = $this->getCsrfToken('/admin/schedule/add');
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters([
                    'event' => $event,
                    'csrf' => $token,
                ])
        );
        $this->dispatch('/admin/schedule/add');
        $this->assertResponseStatusCode(200);
        $response = $this->getResponse()->getBody();
        $obj = json_decode($response);
        $this->assertTrue(is_object($obj),"response is not an object");
        $this->assertTrue(is_array($obj->ids));
        $this->assertEquals(5, count($obj->ids));
        $count_after = $em
          ->createQuery('SELECT COUNT(e.id) FROM InterpretersOffice\Entity\Event e')
          ->getSingleScalarResult();
        $this->assertEquals(
            5, $count_after - $count_before,
            "count $count_after was not incrememented by 5 (from $count_before)"
        );
    }

    public function testNonAnonymousHatRequiresSubmitterIdentification()
    {
         // $data = [];
         $em = FixtureManager::getEntityManager();

        
         $event = $this->getDummyData();
         $event['submitter'] = ''; // should be sufficient to trigger validation error
         //$event['anonymous_submitter']
         $this->login('david', 'boink');
         $this->reset(true);
         $token = $this->getCsrfToken('/admin/schedule/add');
         $this->getRequest()->setMethod('POST')->setPost(
             new Parameters([
                     'event' => $event,
                     'csrf' => $token,
                 ])
         );
         $this->dispatch('/admin/schedule/add');
         $this->assertJson($this->getResponse()->getBody());
         $res = json_decode($this->getResponse()->getBody());
        //  print_r($res);
         $this->assertTrue(isset($res->validation_errors));
         $this->assertTrue(isset($res->validation_errors->event->submitter));
    }
    /**
     * @depends testAddInCourtEvent
     *
     */
    public function testUpdateInCourtEvent(Entity\Event $entity)
    {
        $em = FixtureManager::getEntityManager();
        // sanity check: refresh entity and check no defendants as yet
        //$entity = $em->find(Entity\Event::class,$entity->getId());
        $this->assertEquals(0, $entity->getDefendants()->count());
        $event = $this->getDummyData();
        $this->login('david', 'boink');
        $this->reset(true);
        $token = $this->getCsrfToken('/admin/schedule/add');
        $this->dispatch(
            '/admin/schedule/add',
            'POST',
            ['event' => $event,'csrf' => $token]
        );
        $count_after = $em
          ->createQuery('SELECT COUNT(e.id) FROM InterpretersOffice\Entity\Event e')
          ->getSingleScalarResult();
        // sanity check
        $this->assertEquals(2, (integer)$count_after);
        $id = $em->createQuery('SELECT MAX(e.id) FROM InterpretersOffice\Entity\Event e')
        ->getSingleScalarResult();//$count_after; // as it so happens
        $this->reset(true);
        $this->login('david', 'boink');
        $this->reset(true);
        $url = '/admin/schedule/edit/'.$id;
        $this->dispatch($url);
        // $this->dumpResponse(); return;
        $this->assertQueryCount('form#event-form', 1);
        $dom = new Dom\Query($this->getResponse()->getBody());
        $element = $dom->execute('#time')->current();
        $time = $element->getAttribute('value');
        $time_expected = $entity->getTime()->format('g:i a');
        $this->assertEquals(html_entity_decode($time), $time_expected);

        $date_expected = $entity->getDate()->format('m/d/Y');
        $date = $dom->execute('#date')->current()->getAttribute('value');
        $this->assertEquals(html_entity_decode($date), $date_expected);

        $judge_select = $dom->execute('#judge')->current();
        $judge_options = $judge_select->childNodes;
        $judge_lastname = $entity->getJudge()->getLastname();
        $found = false;
        foreach ($judge_options as $opt) {
            $name = $opt->nodeValue;
            if (false !== strstr($name, $judge_lastname)) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
        $this->assertTrue($opt->hasAttribute('selected'));
        $this->assertEquals($opt->getAttribute('selected'), 'selected');

        $language_select = $dom->execute('#language')->current();
        $expected = $entity->getLanguage()->getName();
        $this->assertOptionIsSelected($language_select, $expected);

        $type_expected = (string)$entity->getEventType();
        $type_select = $dom->execute('#event_type')->current();
        $this->assertOptionIsSelected($type_select, $type_expected);

        $expected = $entity->getLocation()->getParentLocation()->getName();
        $parent_location_select = $dom->execute('#parent_location')->current();
        $this->assertOptionIsSelected($parent_location_select, $expected);

        $expected_location = $entity->getLocation()->getName();
        $location_select = $dom->execute('#location')->current();
        $this->assertOptionIsSelected($location_select, $expected_location);
        $submitter = $entity->getSubmitter();
        $hat = (string)$submitter->getHat();
        $hat_select = $dom->execute('#hat')->current();
        $this->assertOptionIsSelected($hat_select, $hat);

        $expected_person = sprintf('%s, %s', $submitter->getLastname(), $submitter->getFirstname());
        $submitter_select = $dom->execute('#submitter')->current();
        $this->assertOptionIsSelected($submitter_select, $expected_person);
        $submission_date_element = $dom->execute('#submission_date')->current();
        $submission_date = $submission_date_element->getAttribute('value');
        $expected = $entity->getSubmissionDate()->format('m/d/Y');
        $this->assertEquals($expected, $submission_date);

        $submission_time_element = $dom->execute('#submission_time')->current();
        $submission_time = $submission_time_element->getAttribute('value');
        $expected = $entity->getSubmissionTime()->format('g:i a');
        $this->assertEquals($expected, $submission_time);

        # try changing type to plea, hour to 3:00p
        #
        $event['time'] = '3:00 pm';
        $type_id = $em->getRepository(Entity\EventType::class)
                ->findOneBy(['name' => 'plea'])->getId();
        $event['event_type'] = $type_id;
        $event['id'] = $count_after;
        $event['end_time'] = '';
        $event['comments'] = 'hey this is something different';
        // cheat
        $modified = $em->createQuery('SELECT e.modified '
                . 'FROM InterpretersOffice\Entity\Event e '
                . 'WHERE e.id = :id')->setParameters(['id' => $count_after])
                ->getSingleScalarResult();


        //['Fulano Mengano', 'Joaquín'],
        $deft_id = $em->getRepository(Entity\Defendant::class)->findOneBy(
            ['surnames' => 'Fulano Mengano','given_names' => 'Joaquín' ]
        )->getId();
        $event['defendants'][] = $deft_id;
        $this->reset(true);
        $this->login('david', 'boink');
        $this->reset(true);
        $token = $this->getCsrfToken($url);
        $this->dispatch(
            $url,
            'POST',
            ['event' => $event, 'csrf' => $token, 'modified' => $modified]
        );
        //$this->dumpResponse();
        $content = $this->getResponse()->getContent();
        $this->assertJson($content);
        $response = json_decode($content);
        $this->assertEquals("success", $response->status);

        $type_before = $type_expected;
        $time_before = $time_expected;
        $shit = $em->getRepository(Entity\Event::class)->getView($id)['event'];
        $this->assertTrue('plea' == $shit['type']);
        $this->assertNotEquals($type_before, $shit['type']);
        if (is_object($shit['time'])) {
            $time_after = $shit['time']->format('g:i a');
            $this->assertEquals($event['time'], $time_after);
            $this->assertNotEquals($time_before, $time_after);
        } else {
            printf("\nwarning: can't test because we don't know what format to "
                    . "expect for event 'time' property in %s\n", __METHOD__);
        }
        // did the defendant get added?
        $entity = $em->find(Entity\Event::class, $id);
        $defts = $entity->getDefendants();
        $this->assertEquals(1, $defts->count());
    }

    public function testLoadEventUpdateForm()
    {
       $this->login('david', 'boink');
       $this->reset(true);
       $this->dispatch('/admin/schedule/edit/1');
        $this->assertResponseStatusCode(200);
        $this->assertQuery('#event-form');
        $this->assertQueryCount('ul.interpreters-assigned li', 1);
        $this->assertQueryContentRegex('ul.interpreters-assigned li',"/Mintz, David/");
    }
    public function testAssigningInterpretersResultsInMetaDataUpdate()
    {
        $db = FixtureManager::getEntityManager()->getConnection();
        $then = (new \DateTime('-10 minutes'))->format("Y-m-d H:i:s");
        $db->executeQuery("UPDATE events SET modified = '$then' WHERE id = 1");

        $entity = FixtureManager::getEntityManager()->find('InterpretersOffice\Entity\Event',1);
        //$defts = $entity->getDefendants()->toArray();

        $data = [
            'judge' => $entity->getJudge()->getId(),
            'language' => $entity->getLanguage()->getId(),
            'date' => $entity->getDate()->format("m/d/Y"),
            'time' => $entity->getTime()->format("g:i a"),
            'docket' => $entity->getDocket(),
            'event_type' => $entity->getEventType()->getId(),
            'location' =>$entity->getJudge()->getDefaultLocation()->getId(),
            'parentLocation' => '',
            'submission_date' => $entity->getSubmissionDate()->format("m/d/Y"),
            'submission_time' => $entity->getSubmissionTime()->format("g:i a"),
            'anonymous_submitter' => '',
            'submitter' => $entity->getSubmitter()->getId(),
            'anonymous_judge' => '',
            'is_anonymous_judge' => '',
            'cancellation_reason' => '',
            'id' => $entity->getId(),
            'defendants' => [
                $entity->getDefendants()->toArray()[0]->getId()
            ],

        ];
        $interpreter_id = FixtureManager::getEntityManager()->createQuery(
             'SELECT i.id FROM InterpretersOffice\Entity\Interpreter i WHERE i.lastname = :lastname'
        )->setParameters([':lastname'=>'Somebody'])->getSingleScalarResult();
        $data['interpreterEvents'] = array(
            [
                'interpreter' => $interpreter_id,
                'event'       => $entity->getId(),
            ],
            [
                'interpreter' => $entity->getInterpreters()[0]->getId(),
                'event'       => $entity->getId(),            ]
        );
        $url = "/admin/schedule/edit/{$data['id']}";
        $this->login('david', 'boink');
        $this->reset(true);
        $token = $this->getCsrfToken($url, 'csrf');
        //printf("\nthe fucking token is %s\n",$token);
        $data['anonymous_submitter'] = 0;
        $this->getRequest()
            ->setMethod('POST')->setPost(
            new Parameters([
                'event' => $data,
                'csrf' => $token,
                'modified' => $entity->getModified()->format('Y-m-d H:i:s'),
            ])
        )->getHeaders()->addHeaderLine('X-Requested-With','XMLHttpRequest');

        $this->dispatch($url);
        $this->assertResponseStatusCode(200);
        $json = $this->getResponse()->getBody();
        $response = json_decode($json);
        $this->assertTrue($response->status == "success");
        $now =  $entity = FixtureManager::getEntityManager()->find('InterpretersOffice\Entity\Event',1)
            ->getModified()->format('Y-m-d H:i:s');
        // $this->dumpResponse();
        $this->assertNotEquals($then,$now);
        /**/

    }

    public function testEventInputValidation()
    {
        $em = FixtureManager::getEntityManager();
        $event = $this->getDummyData();
        $this->login('david', 'boink');
        $this->reset(true);
        $token = $this->getCsrfToken('/admin/schedule/add');

        // whatever the event date is, try making the submission date 1 day later
        // i.e., impossible

        $event['submission_date'] = (new \DateTime("$event[date] + 1 day"))->format('Y-m-d');
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters([
                    'event' => $event,
                    'csrf' => $token,
                ])
        );
        $count_before = $em
          ->createQuery('SELECT COUNT(e.id) FROM InterpretersOffice\Entity\Event e')
          ->getSingleScalarResult();
        $this->dispatch('/admin/schedule/add');
        $count_after = $em
          ->createQuery('SELECT COUNT(e.id) FROM InterpretersOffice\Entity\Event e')
          ->getSingleScalarResult();
        $this->assertEquals(
            $count_before, $count_after,
            'Event count was incremented where insertion should have failed'
        );
    }

    public function testEventSoftDeletion()
    {
        $em = FixtureManager::getEntityManager();
        $id = $em->createQuery('SELECT MAX(e.id) FROM InterpretersOffice\Entity\Event e
            WHERE e.deleted = false')
            ->getSingleScalarResult();
        $count_was =  $em->createQuery('SELECT COUNT(e.id) FROM InterpretersOffice\Entity\Event e')
            ->getSingleScalarResult();
        $this->assertTrue(is_numeric($id));
        $this->login('david', 'boink');
        $this->reset(true);
        //$this->dispatch('/admin/schedule/view/'.$id);
        $token = $this->getCsrfToken('/admin/schedule/edit/'.$id);
        $this->dispatch("/admin/schedule/delete/$id",'POST',['csrf'=>$token],true);
        $this->assertResponseStatusCode(200);
        $response = json_decode($this->getResponse()->getBody());
        $this->assertTrue(is_object($response));
        $this->assertEquals("success",$response->status);
        $count_is =  $em->createQuery('SELECT COUNT(e.id) FROM InterpretersOffice\Entity\Event e')
            ->getSingleScalarResult();
        $this->assertEquals($count_is,$count_was);
        $entity = $em->createQuery('SELECT e FROM InterpretersOffice\Entity\Event e
                WHERE e.id = :id')->setParameters([':id'=>$id])
            ->getOneOrNullResult();
        $this->assertTrue(is_object($entity));
        $this->assertTrue($entity->isDeleted());
    }

    public function testEventEmailConfirmationStatusIsTurnedOffWhenEventIsCancelled(){

        $em = FixtureManager::getEntityManager();
        /** @var InterpretersOffice\Entity\Event $event */
        $event = $em->getRepository(Entity\Event::class)->findOneBy(['docket'=>\ApplicationTest\DataFixture\EventLoader::DUMMY_DOCKET]);
        $interpreter_events = $event->getInterpreterEvents();
        foreach( $interpreter_events as $ie) {
            $ie->setSentConfirmationEmail(true);
        }
        $em->flush();
        // sanity checks
        foreach($interpreter_events as $ie) {
            $this->assertTrue($ie->getSentConfirmationEmail());
        }
        $this->assertNull($event->getCancellationReason());
        $id = $event->getId();
        // now change it to cancelled
        $belated_cancellation = $em->getRepository('InterpretersOffice\Entity\ReasonForCancellation')
        ->findOneBy(['reason'=>'belatedly adjourned']);
        $data = [
            'cancellation_reason' => $belated_cancellation->getId(), // here's what's signicant
            'judge' => $event->getJudge()->getId(),
            'language' => $event->getLanguage()->getId(),
            'date'=> $event->getDate()->format("m/d/Y"),
            'docket'=> $event->getDocket(),
            'time'=> '10:00 am',
            'event_type' => $event->getEventType()->getId(),
            'is_anonymous_judge'=>'',
            'anonymous_submitter'=>'',
            'submitter'=>$event->getSubmitter()->getId(),
            'submission_date'=>$event->getSubmissionDate()->format("m/d/Y"),
            'submission_time'=>$event->getSubmissionTime()->format("g:i a"),
            'id' => $id,
            'interpreterEvents' => [
                ['interpreter' => $event->getInterpreters()[0]->getId(),
                'event'  => $id,
                ],
            ],
            'defendants' =>array_map(function($d){return $d->getId();},$event->getDefendants()->toArray()),
        ];
        
        $url = '/admin/schedule/edit/'.$id;
        $this->reset(true);
        $this->login('david', 'boink');
        $this->reset(true);
        $token = $this->getCsrfToken($url);
        $data['anonymous_submitter'] = 0;
        $this->dispatch(
            $url,
            'POST',
            ['event' => $data, 
            'csrf' => $token, 
            'modified' => $event->getModified()->format('Y-m-d H:i:s')
            ], true
        );
        $content = $this->getResponse()->getContent();
        $this->assertJson($content);
        $response = json_decode($content);
        $this->assertTrue($response->status == 'success');
        // print_r($response);
        // moment of truth
        $em->refresh($event);
        $interpreter_events = $event->getInterpreterEvents();
        $this->assertTrue($interpreter_events->count() > 0);
        foreach ($interpreter_events as $ie) {
            $this->assertFalse($ie->getSentConfirmationEmail());
        }       
    }

    public function testEventEmailConfirmationStatusIsTurnedOffWhenDateIsChanged()
    {
        $em = FixtureManager::getEntityManager();
        /** @var InterpretersOffice\Entity\Event $event */
        $event = $em->getRepository(Entity\Event::class)->findOneBy(['docket'=>\ApplicationTest\DataFixture\EventLoader::DUMMY_DOCKET]);
        $interpreter_events = $event->getInterpreterEvents();
        foreach( $interpreter_events as $ie) {
            $ie->setSentConfirmationEmail(true);
        }
        $em->flush();
        // sanity checks
        foreach($interpreter_events as $ie) {
            $this->assertTrue($ie->getSentConfirmationEmail());
        }
        $this->assertNull($event->getCancellationReason());
        $id = $event->getId();
        
        // now change the date
        $new_date = $event->getDate()->add(new \DateInterval('P7D'));
        $data = [
            'cancellation_reason' => '',//$event->getCancellationReason()->getId(),
            'judge' => $event->getJudge()->getId(),
            'language' => $event->getLanguage()->getId(),
            'date'=> $new_date->format("m/d/Y"),
            'docket'=> $event->getDocket(),
            'time'=> '10:00 am',
            'event_type' => $event->getEventType()->getId(),
            'is_anonymous_judge'=>'',
            'anonymous_submitter'=>'',
            'submitter'=>$event->getSubmitter()->getId(),
            'submission_date'=>$event->getSubmissionDate()->format("m/d/Y"),
            'submission_time'=>$event->getSubmissionTime()->format("g:i a"),
            'id' => $id,
            'interpreterEvents' => [
                ['interpreter' => $event->getInterpreters()[0]->getId(),
                'event'  => $id,
                ],
            ],
            'defendants' =>array_map(function($d){return $d->getId();},$event->getDefendants()->toArray()),
        ];
        
        $url = '/admin/schedule/edit/'.$id;
        $this->reset(true);
        $this->login('david', 'boink');
        $this->reset(true);
        $token = $this->getCsrfToken($url);
        $data['anonymous_submitter'] = 0;
        $this->dispatch(
            $url,
            'POST',
            ['event' => $data, 
            'csrf' => $token, 
            'modified' => $event->getModified()->format('Y-m-d H:i:s')
            ], true
        );
        $content = $this->getResponse()->getContent();
        $this->assertJson($content);
        $response = json_decode($content);
        $this->assertTrue($response->status == 'success');

        // moment of truth
        $em->refresh($event);
        $interpreter_events = $event->getInterpreterEvents();
        $this->assertTrue($interpreter_events->count() > 0);
        foreach ($interpreter_events as $ie) {
            $this->assertFalse($ie->getSentConfirmationEmail());
        }       
    }
}
