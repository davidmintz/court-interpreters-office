<?php

/** module/Application/test/Controller/InterpretersControllerTest.php */

namespace ApplicationTest\Controller;

use ApplicationTest\AbstractControllerTest;
use InterpretersOffice\Admin\Controller\InterpretersController;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;
use Zend\Stdlib\Parameters;
use Zend\Dom\Query;
use InterpretersOffice\Entity;

/**
 * test interpreters controller.
 *
 * 
 */
class InterpretersControllerTest extends AbstractControllerTest
{
    public function setUp()
    {
        parent::setUp();
        $fixtureExecutor = FixtureManager::getFixtureExecutor();
        $fixtureExecutor->execute(
            [
            new DataFixture\MinimalUserLoader(),
            new DataFixture\LanguageLoader(),
            new DataFixture\InterpreterLoader(),
            ]
        );

        $this->login('susie', 'boink');
    }
    
    public function testIndexAction()
    {
        $this->dispatch('/admin/interpreters');
        $this->assertResponseStatusCode(200);
    }
    
    public function testUpdateInterpreter()
    {
        // what is the id of an interpreter?
        $em = FixtureManager::getEntityManager();
        $interpreter = $em->getRepository('InterpretersOffice\Entity\Interpreter')
                ->findOneBy(['lastname' => 'Mintz']);
        // sanity-check
        $this->assertInstanceOf(Entity\Interpreter::class, $interpreter);
        
        $url = '/admin/interpreters/edit/'.$interpreter->getId();
        
        $this->dispatch($url);
        $this->assertQuery('form');
        $this->assertQuery('#lastname');
        $this->assertQuery('#firstname');
        $this->assertQuery('#middlename');
        $query = new Query($this->getResponse()->getBody());
        $node1 = $query->execute('#lastname')->current();
        $lastname = $node1->attributes->getNamedItem('value')->nodeValue;
        $this->assertEquals('Mintz', $lastname);
        
        // add Russian
        $russian = $interpreter = $em->getRepository('InterpretersOffice\Entity\Language')
                ->findOneBy(['name' => 'Russian']);
        $this->assertInstanceOf(Entity\Language::class, $russian);
        
        // to be continued
    }

}