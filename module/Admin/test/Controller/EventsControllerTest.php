<?php
namespace ApplicationTest\Controller;

use ApplicationTest\AbstractControllerTest;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;

use InterpretersOffice\Entity;
use Zend\Stdlib\Parameters;

class EventControllerTest extends AbstractControllerTest
{
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
        $this->assertQueryCount('#date',1);
        $this->assertQueryCount('#time',1);
        $this->assertQueryCount('#event-type',1);
        $this->assertQueryCount('#judge',1);
        $this->assertQueryCount('#language',1);
        $this->assertQueryCount('#docket',1);
        $this->assertQueryCount('#location',1);
        /** to be continued */
        
    }
    
    public function testAddInCourtEvent()
    {
        $data = [];
        $em =  FixtureManager::getEntityManager();
        
        $count_before = $em
          ->createQuery('SELECT COUNT(e.id) FROM InterpretersOffice\Entity\Event e')
          ->getSingleScalarResult();
        $judge = $em->getRepository(Entity\Judge::class)
                ->findOneBy(['lastname' => 'Dinklesnort']);

        $data['judge'] = $judge->getId();
        // $this->assertTrue(is_integer($data['judge']));
        $language = $em->getRepository(Entity\Language::class)
                ->findOneBy(['name' => 'Spanish']);
        $data['language'] = $language->getId();
        
        $data['date'] = (new \DateTime("next Monday"))->format("m/d/Y");
        $data['time'] = '10:00 am';
        
        $data['docket'] = '2017 CR 123';
        
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
        
        $data['submitter'] = $user->getId();
        $data['anonymousJudge'] = '';
        $data['id'] = '';
        
        $this->assertTrue(is_array($data));
        $this->login('david', 'boink');
        $this->reset(true);
        $token = $this->getCsrfToken('/admin/schedule/add');
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters(
                [
                    'event' => $data,
                    'csrf' => $token,
                ]
            )
        );
        $this->dispatch('/admin/schedule/add');
        $count_after = $em
          ->createQuery('SELECT COUNT(e.id) FROM InterpretersOffice\Entity\Event e')
          ->getSingleScalarResult();
        
        $this->assertEquals(1, $count_after - $count_before, 
                "count was not incrememented by one");
        
    }
        
}
