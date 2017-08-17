<?php

/** module/Application/test/Controller/InterpretersControllerTest.php */

namespace ApplicationTest\Controller;

use ApplicationTest\AbstractControllerTest;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;
use Zend\Stdlib\Parameters;
use Zend\Dom\Document;
use InterpretersOffice\Entity;

/**
 * test interpreters controller.
 */
class InterpretersControllerTest extends AbstractControllerTest {

    public function setUp() {
        parent::setUp();
        $fixtureExecutor = FixtureManager::getFixtureExecutor();

        $fixtureExecutor->execute(
                [
                    new DataFixture\MinimalUserLoader(),
                    new DataFixture\LanguageLoader(),
                    new DataFixture\InterpreterLoader(),
                ]
        );
        //echo "\nsetUp ran login()...\n";
        $this->login('susie', 'boink');  
        $this->reset(true);
        $auth = $this->getApplicationServiceLocator()->get('auth');
        $RESULT = $auth->hasIdentity() ?  "SUCCESS" : "FAILED";
        printf("\nDEBUG: %s: login $RESULT in %s:%d\n",__FUNCTION__,basename(__FILE__),__LINE__);
    }

    public function testAddInterpreter() {
        
        $em = FixtureManager::getEntityManager();
        $url = '/admin/interpreters/add';
        
        $russian = $em->getRepository('InterpretersOffice\Entity\Language')->findOneBy(['name' => 'Russian']);
        
        $this->login('susie', 'boink');  
        $this->reset(true);
        // authentication needs to have succeeded here.
        $auth = $this->getApplicationServiceLocator()->get('auth');
        if (! $auth->hasIdentity()) {
            exit("SHIT FAILED!\n" . $this->getResponse()->getBody());
        }
        $token =  $this->getCsrfToken($url,'csrf'); 
        
        
        $count_before = $em->createQuery('SELECT COUNT(i.id) FROM InterpretersOffice\Entity\Interpreter i')
                ->getSingleScalarResult();
        $data = [
            'interpreter' => [
                'lastname' => 'Snertsky',
                'firstname' => 'David',
                'hat' => $em->getRepository('InterpretersOffice\Entity\Hat')
                        ->findOneBy(['name' => 'contract court interpreter'])->getId(),
                'email' => 'snyert@example.org',
                'active' => 1,
                'id' => '',
                'dob' => '',
                'ssn' => '',
                'language-select' => 1,
                'interpreter-languages' => [
                    [
                        'language_id' => $russian->getId(),
                        'interpreter_id' => '',
                        'federalCertification' => '-1',
                    ],
                ],
            ],
            'csrf' => $token,
        ];
        $this->getRequest()->setMethod('POST')->setPost(
                new Parameters($data)
        );
        $this->dispatch($url);

        $this->assertRedirect();
        $this->assertRedirectTo('/admin/interpreters');

        $count_after = $em->createQuery('SELECT COUNT(i.id) FROM InterpretersOffice\Entity\Interpreter i')
                ->getSingleScalarResult();

        $this->assertEquals($count_after, $count_before + 1);
        
        //echo "\nDEBUG: exiting ".__FUNCTION__."\n";
    }

    public function testIndexAction() { 
        $this->dispatch('/admin/interpreters');
        //echo $this->getResponse()->getBody(); return;
        $this->assertResponseStatusCode(200);
    }

    public function testUpdateInterpreter() { 

        // what is the id of an interpreter?
        $em = FixtureManager::getEntityManager();
        //$listener = $this->getApplicationServiceLocator()->get("interpreter-listener");
        //$em->getConfiguration()->getEntityListenerResolver()->register($listener);
        $interpreter = $em->getRepository('InterpretersOffice\Entity\Interpreter')
                ->findOneBy(['lastname' => 'Mintz']);

        $this->assertInstanceOf(Entity\Interpreter::class, $interpreter);
        $id = $interpreter->getId();
        $url = '/admin/interpreters/edit/' . $id;
        $this->login('susie', 'boink');  
        $this->reset(true);
        $this->dispatch($url);
        //echo "\nresponse status code: " .$this->getResponseStatusCode() . "\n";
        //$body=$this->getResponse()->getBody();
        //print "BODY: $body"; return;
        $this->assertQuery('form');
        $this->assertQuery('#lastname');
        $this->assertQuery('#firstname');
        $this->assertQuery('#middlename');
        
        $document = new Document($this->getResponse()->getBody(),Document::DOC_HTML);
        //$document->setStringDocument($html);
        $query = new Document\Query(); 
  
        //$results = $query->execute('#lastname',$document,  Document\Query::TYPE_CSS);
        //$query = new Query($this->getResponse()->getBody());
        $node1 = $query->execute('#lastname',$document, Document\Query::TYPE_CSS)->current();
        $lastname = $node1->attributes->getNamedItem('value')->nodeValue;
        $this->assertEquals('Mintz', $lastname);

        // should have one language (Spanish)
        $this->assertQueryCount('div.language-name', 1);
        $this->assertQueryContentContains('div.language-name', 'Spanish');

        // and it should have federal certification == yes
        $nodeList = $query->execute('div.language-certification > select > option',$document, Document\Query::TYPE_CSS);
        foreach ($nodeList as $element) {
            if ($element->getAttributeNode('selected')) {
                break;
            }
        }
        $this->assertInstanceOf(\DOMElement::class, $element);
        $this->assertEquals($element->getAttributeNode('selected')->value, 'selected');
        $this->assertEquals(strtolower($element->nodeValue), 'yes');
        $this->assertEquals($element->getAttributeNode('value')->value, '1');

        $russian = $em->getRepository('InterpretersOffice\Entity\Language')
                ->findOneBy(['name' => 'Russian']);
        $this->assertInstanceOf(Entity\Language::class, $russian);
        $spanish = $em->getRepository('InterpretersOffice\Entity\Language')
                ->findOneBy(['name' => 'Spanish']);

        $data = [
            'interpreter' => [
                'lastname' => 'Mintz',
                'firstname' => 'David',
                'hat' => $em->getRepository('InterpretersOffice\Entity\Hat')
                        ->findOneBy(['name' => 'staff court interpreter'])->getId(),
                'email' => 'david@davidmintz.org',
                'active' => 1,
                'id' => $id,
                'language-select' => 1,
                'interpreter-languages' => [
                    [
                        'language_id' => $spanish->getId(),
                        'interpreter_id' => $id,
                        'federalCertification' => 1,
                    ],
                    [
                        'language_id' => $russian->getId(),
                        'interpreter_id' => $id,
                        'federalCertification' => '',
                    ],
                ],
            ],
            'csrf' => $this->getCsrfToken($url),
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
        $this->assertQueryCount('div.language-name', 2);
        // ...one of which is Russian
        $selector = '#language-' . $russian->getId();
        $this->assertQuery($selector);
        $this->assertQueryContentContains($selector, 'Russian');

        // now take it out
        unset($data['interpreter']['interpreter-languages'][1]);
        // PLEASE do not forget this.
        // @todo:  make sure CSRF error thing is in the damn viewscript!
        $data['csrf'] = $this->getCsrfToken($url);
        $this->getRequest()->setMethod('POST')->setPost(
                new Parameters($data)
        );
        $this->dispatch($url);
        $this->assertRedirect();
        $this->assertRedirectTo('/admin/interpreters');

        // load the form again
        $this->reset(true);
        $this->dispatch($url);
        
        // there should now be one language again
        $this->assertQueryCount('div.language-name', 1);
        $this->assertQueryContentContains('div.language-name', 'Spanish');
        
    }

}
