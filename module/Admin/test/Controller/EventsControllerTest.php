<?php
namespace ApplicationTest\Controller;

use ApplicationTest\AbstractControllerTest;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;

use InterpretersOffice\Entity;
use Zend\Stdlib\Parameters;

use Zend\Dom;

class EventControllerTest extends AbstractControllerTest
{

    protected $dummy_data;

    public function setUp()
    {
        parent::setUp();
        FixtureManager::dataSetup();

        $this->login('david', 'boink');
        $this->reset(true);
    }

    protected function getDummyData()
    {
        if ($this->dummy_data) {
            return $this->dummy_data;
        }
        $data = [];
        $em = FixtureManager::getEntityManager();
        $judge = $em->getRepository(Entity\Judge::class)
                ->findOneBy(['lastname' => 'Dinklesnort']);
        $data['judge'] = $judge->getId();
        // $this->assertTrue(is_integer($data['judge']));
        $language = $em->getRepository(Entity\Language::class)
                ->findOneBy(['name' => 'Spanish']);
        $data['language'] = $language->getId();

        $data['date'] = (new \DateTime("next Monday"))->format("m/d/Y");
        $data['time'] = '10:00 am';
        $data['docket'] = '2017-CR-123';
        $type = $em->getRepository(Entity\EventType::class)->findOneBy(['name' => 'conference']);
        $data['eventType'] = $type->getId();
        $location = $em->getRepository(Entity\Location::class)
                ->findOneBy(['name' => '14B']);
        $data['location'] = $location->getId();
        $parent_location = $em->getRepository(Entity\Location::class)
                ->findOneBy(['name' => '500 Pearl']);
        $data['parentLocation'] = $parent_location->getId();
        $data['submission_date'] = (new \DateTime('-1 day'))->format("m/d/Y");
        $data['submission_time'] = '9:43 am';//(new \DateTime('-5 minutes'))->format("g:i a");
        $clerk_hat = $em->getRepository(Entity\Hat::class)
                ->findOneBy(['name' => 'Law Clerk']);
        $data['anonymousSubmitter'] = $clerk_hat->getId();
        $dql = 'SELECT u FROM InterpretersOffice\Entity\User u JOIN u.person p '
                . ' WHERE p.email = :email';
        $user = $em->createQuery($dql)
            ->setParameters(['email' => 'jane_zorkendoofer@nysd.uscourts.gov'])
            ->getOneorNullResult();
        $data['submitter'] = $user->getPerson()->getId();
        $data['anonymousJudge'] = '';
        $data['is_anonymous_judge'] = '';
        $data['cancellationReason'] = '';
        $data['id'] = '';

        $this->dummy_data = $data;
        return $data;
    }


    public function testLoadEventInsertForm()
    {
        $this->dispatch('/admin/schedule/add');
        $this->assertResponseStatusCode(200);
        $this->assertQueryCount('form#event-form', 1);
        $this->assertQueryCount('#date', 1);
        $this->assertQueryCount('#time', 1);
        $this->assertQueryCount('#event-type', 1);
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
        $this->assertQueryCount('#anonymousJudge', 1);
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
        $this->assertRedirect();
        $this->assertEquals(
            1,
            $count_after - $count_before,
            "count $count_after was not incrememented by one (from $count_before)"
        );
        // pull it out and take a look.
        $q = 'SELECT MAX(e.id) FROM InterpretersOffice\Entity\Event e';
        $id = $em->createQuery($q)->getSingleScalarResult();
        $data = $em->getRepository(Entity\Event::class)->getView($id);
        $this->assertTrue(false !== strstr($data['judge'], 'Dinklesnort'));
        $this->assertTrue(false !== strstr($data['type'], 'conference'));
        $this->assertEquals($data['location'], '14B');
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

    /**
     * @depends testAddInCourtEvent
     *
     */
    public function testUpdateInCourtEvent(Entity\Event $entity)
    {
        $em = FixtureManager::getEntityManager();
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
        $id = $count_after; // as it so happens
        $this->reset(true);
        $this->login('david', 'boink');
        $this->reset(true);
        $url = '/admin/schedule/edit/'.$id;
        $this->dispatch($url);
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
        $type_select = $dom->execute('#event-type')->current();
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
        $event['eventType'] = $type_id;
        $event['id'] = $count_after;
        $event['end_time'] = '';
        $event['comments'] = 'hey this is something different';
        // cheat
        $modified = $em->createQuery('SELECT e.modified '
                . 'FROM InterpretersOffice\Entity\Event e '
                . 'WHERE e.id = :id')->setParameters(['id' => $count_after])
                ->getSingleScalarResult();

        $this->reset(true);
        $this->login('david', 'boink');
        $this->reset(true);
        $token = $this->getCsrfToken($url);
        $this->dispatch(
            $url,
            'POST',
            ['event' => $event, 'csrf' => $token, 'modified' => $modified]
        );
        $this->assertRedirect();
        $this->assertRedirectTo('/admin/schedule');

        $type_before = $type_expected;
        $time_before = $time_expected;
        $shit = $em->getRepository(Entity\Event::class)->getView($id);
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
            $count_before,
            $count_after,
            'Event count was incremented where insertion should have failed'
        );
    }

    public function _testGetView()
    {
    }
}
