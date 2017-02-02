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

        $this->assertInstanceOf(Entity\Interpreter::class, $interpreter);
        $id = $interpreter->getId();
        $url = '/admin/interpreters/edit/'.$id;
       
        $this->dispatch($url);
        $this->assertQuery('form');
        $this->assertQuery('#lastname');
        $this->assertQuery('#firstname');
        $this->assertQuery('#middlename');
        $query = new Query($this->getResponse()->getBody());
        $node1 = $query->execute('#lastname')->current();
        $lastname = $node1->attributes->getNamedItem('value')->nodeValue;
        $this->assertEquals('Mintz', $lastname);
        
        // should have one language (Spanish)
        $this->assertQueryCount('div.language-name',1);
        $this->assertQueryContentContains('div.language-name','Spanish');
        
        // and it should have federal certification == yes
        $nodeList = $query->execute('div.language-fed-certification > select > option');
        foreach ($nodeList as $element) {
            if ($element->getAttributeNode('selected')) {
                break;
            }
        }
        $this->assertInstanceOf(\DOMElement::class,$element);
        $this->assertEquals($element->getAttributeNode('selected')->value,'selected');
        $this->assertEquals(strtolower($element->nodeValue), 'yes');
        $this->assertEquals($element->getAttributeNode('value')->value,"1");

        $russian = $em->getRepository('InterpretersOffice\Entity\Language')
                ->findOneBy(['name' => 'Russian']);
        $this->assertInstanceOf(Entity\Language::class, $russian);
        $spanish =  $interpreter = $em->getRepository('InterpretersOffice\Entity\Language')
                ->findOneBy(['name' => 'Spanish']);
       
        $data = [
            'interpreter' => [
                'lastname' => 'Mintz',
                'firstname' => 'David',
                'hat'  =>  $em->getRepository('InterpretersOffice\Entity\Hat')
                    ->findOneBy(['name' => 'staff court interpreter'])->getId(),
                'email' => 'david@davidmintz.org',
                'active' => 1,
                'id' => $id,
                'language-select'=> 1,
                'interpreter-languages' => [
                    [ 
                        'language_id'=> $spanish->getId(),
                        'interpreter_id' => $id,
                        'federalCertification' => 1,
                    ],
                    [
                        'language_id'=> $russian->getId(),
                        'interpreter_id' => $id,
                        'federalCertification' => '',
                    ],                 
                ],
            ],
            'csrf' => $this->getCsrfToken($url)
        ];
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($data)
        );
        $this->dispatch($url);
    
       //echo $this->getResponse()->getBody();
       $this->assertRedirect();
       $this->assertRedirectTo('/admin/interpreters');
       
       // load the form again
       $this->reset(true);
       $this->dispatch($url);
       //there should now be two languages
       $this->assertQueryCount('div.language-name',2);
       // one of which is Russian
       $selector = '#language-'.$russian->getId();
       $this->assertQuery($selector);
       $this->assertQueryContentContains($selector, 'Russian');
       
       // now take it out
       unset($data['interpreter']['interpreter-languages'][1]);
       
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($data)
        );
       $this->dispatch($url);
       
       $this->assertRedirect();
       $this->assertRedirectTo('/admin/interpreters');
       
       // load the form again
       $this->reset(true);
       $this->dispatch($url);
       //there should now be one language again
       $this->assertQueryCount('div.language-name',1);
       $this->assertQueryContentContains('div.language-name','Spanish');
       
       
    }

}