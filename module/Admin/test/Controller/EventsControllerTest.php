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
        $em =  FixtureManager::getEntityManager();
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
        
        $type = $em->getRepository(Entity\EventType::class)->findOneBy(['name'=>'conference']);
        $data['eventType'] = $type->getId();
        
        $location =  $em->getRepository(Entity\Location::class)
                ->findOneBy(['name'=>'14B']);        
        $data['location'] = $location->getId();
        
        $parent_location = $em->getRepository(Entity\Location::class)
                ->findOneBy(['name'=>'500 Pearl']);  
        
        $data['parentLocation'] = $parent_location->getId();
        $data['submission_date'] = (new \DateTime())->format("m/d/Y");
        $data['submission_time'] = (new \DateTime('-5 minutes'))->format("g:i a");
        $clerk_hat =  $em->getRepository(Entity\Hat::class)
                ->findOneBy(['name'=>'Law Clerk']);
        $data['anonymousSubmitter'] = $clerk_hat->getId();
        $dql = 'SELECT u FROM InterpretersOffice\Entity\User u JOIN u.person p '
                . ' WHERE p.email = :email';
        $user = $em->createQuery($dql)
            ->setParameters(['email'=> 'jane_zorkendoofer@nysd.uscourts.gov'])
            ->getOneorNullResult();
        
        $data['submitter'] = $user->getPerson()->getId();
        //printf ("\nDEBUG: submitter is %s, id %d, person_id %d\n",
        //        $user->getPerson()->getLastname(),$user->getId(),$user->getPerson()->getId());
        $data['anonymousJudge'] = '';
        $data['id'] = '';
        
        $this->dummy_data = $data;
        return $data;
        
    }
    
    
    public function testLoadEventInsertForm()
    {
        $this->dispatch('/admin/schedule/add');
        $this->assertResponseStatusCode(200);
        $this->assertQueryCount('form#event-form', 1);
        $this->assertQueryCount('#date',1);
        $this->assertQueryCount('#time',1);
        $this->assertQueryCount('#event-type',1);
        $this->assertQueryCount('#judge',1);
        $this->assertQueryCount('#language',1);
        $this->assertQueryCount('#docket',1);
        $this->assertQueryCount('#location',1);
        $this->assertQueryCount('#parent_location',1);
        /** to be continued ? */
        
    }
    
    public function testAddInCourtEvent()
    {
        //$data = [];
        $em =  FixtureManager::getEntityManager();
        
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
        $this->assertEquals(1, $count_after - $count_before, 
        "count $count_after was not incrememented by one (from $count_before)");
        // pull it out and take a look.
        $q = 'SELECT MAX(e.id) FROM InterpretersOffice\Entity\Event e';
        $id = $em->createQuery($q)->getSingleScalarResult();
        $data = $em->getRepository(Entity\Event::class)->getView($id);
        $this->assertTrue(false !== strstr($data['judge'],'Dinklesnort'));
        $this->assertTrue(false !== strstr($data['type'],'conference'));
        $this->assertEquals($data['location'],'14B');
        $this->assertEquals($data['parent_location'],'500 Pearl');
        $this->assertEquals($data['created_by'],'david');
        $this->assertEquals($data['language'],'Spanish');        
        $this->assertTrue(false !== strstr($data['submitter'],'Zorkendoofer'),
                 "string 'Zorkendoofer' not contained in string '{$data['submitter']}'");
        $this->assertEquals($data['submitter_hat'],'Law Clerk');
        $entity = $em->find(Entity\Event::class,$id);
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
        $this->dispatch('/admin/schedule/add', 'POST',
                ['event'=>$event,'csrf'=>$token]);
        $count_after = $em
          ->createQuery('SELECT COUNT(e.id) FROM InterpretersOffice\Entity\Event e')
          ->getSingleScalarResult();
        // sanity check
        $this->assertEquals(2,(integer)$count_after);
        $this->reset(true);
        $this->login('david', 'boink');
        $this->reset(true);
        $this->dispatch('/admin/schedule/edit/'.$count_after);
        $this->assertQueryCount('form#event-form', 1);
        $dom = new Dom\Query($this->getResponse()->getBody());
        $element = $dom->execute('#time')->current();
        $time = $element->getAttribute('value');
        $time_expected = $entity->getTime()->format('H:i');
        $this->assertEquals(html_entity_decode($time),$time_expected);
        
        $date_expected = $entity->getDate()->format('Y-m-d');
        $date = $dom->execute('#date')->current()->getAttribute('value');
        $this->assertEquals(html_entity_decode($date),$date_expected);
        
        $judge_select = $dom->execute('#judge')->current();
        $judge_options = $judge_select->childNodes;
        $judge_lastname = $entity->getJudge()->getLastname();
        $found = false;
        foreach ($judge_options as $opt) {
            $name = $opt->nodeValue;
            if (false !== strstr($name,$judge_lastname)) {
                $found = true;
                break;
            }            
        }
        $this->assertTrue($found);
        $this->assertTrue($opt->hasAttribute('selected'));
        $this->assertEquals($opt->getAttribute('selected'),'selected');
        
        $language_select = $dom->execute('#language')->current();
        $expected = $entity->getLanguage()->getName();
        
        $this->assertOptionIsSelected($language_select, $expected);

        $expected = (string)$entity->getEventType();
        $type_select = $dom->execute('#event-type')->current();
        $this->assertOptionIsSelected($type_select,$expected);
        
        $expected = $entity->getLocation()->getParentLocation()->getName();
        $parent_location_select = $dom->execute('#parent_location')->current();
        $this->assertOptionIsSelected($parent_location_select,$expected);
        
        $expected_location =  $entity->getLocation()->getName();        
        $location_select = $dom->execute('#location')->current();
        $this->assertOptionIsSelected($location_select, $expected_location);
       
       
        
        
    }
    
   
    
    public function _testGetView()
    {
        
        
    }
        
}
    