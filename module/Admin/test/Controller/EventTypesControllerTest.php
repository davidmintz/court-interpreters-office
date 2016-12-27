<?php

namespace ApplicationTest\Controller;

use InterpretersOffice\Admin\Controller\PeopleController;
use ApplicationTest\AbstractControllerTest;
use Zend\Stdlib\Parameters;
use Zend\Dom\Query;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;
use InterpretersOffice\Entity;

class EventTypesControllerTest extends AbstractControllerTest
{
    
    
    public function setUp()
    {
        
        parent::setUp();
        $fixtureExecutor = FixtureManager::getFixtureExecutor();
        $fixtureExecutor->execute(
            [  
                new DataFixture\MinimalUserLoader(),
                new DataFixture\EventTypeLoader(),
            ]);
        
        $this->login('susie', 'boink');
    }
    
    public function testIndexAction()
    {
        
        $this->dispatch('/admin/event-types');
        $this->assertResponseStatusCode(200);
        
        $count = FixtureManager::getEntityManager()
            ->createQuery('SELECT COUNT(t.id) FROM InterpretersOffice\Entity\EventType t')
            ->getSingleScalarResult();
        $this->assertQuery('#event-types-list');
        $this->assertQueryCount('#event-types-list li',$count);
        
    }
    
    
    
}