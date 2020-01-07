<?php

/** module/Application/test/Controller/InterpretersControllerTest.php */

namespace ApplicationTest\Controller;

use ApplicationTest\AbstractControllerTest;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;
use Laminas\Stdlib\Parameters;
use Laminas\Dom\Document;
use InterpretersOffice\Entity;

/**
 * test interpreters controller.
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
                    new DataFixture\LanguageCredentialLoader(),
                    new DataFixture\InterpreterLoader(),
                ]
        );
        //echo "\nsetUp ran login()...\n";
        $this->login('susie', 'boink');
        $this->reset(true);
        $auth = $this->getApplicationServiceLocator()->get('auth');
        $RESULT = $auth->hasIdentity() ? "SUCCESS" : "FAILED";
        //printf("\nDEBUG: %s: login $RESULT in %s:%d\n",__FUNCTION__,basename(__FILE__),__LINE__);
    }

    public function testAddInterpreter()
    {

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
        //$this->dispatch($url); $this->dumpResponse(); return;
        $token = $this->getCsrfToken($url, 'csrf');


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
                'interpreterLanguages' => [
                    [
                        'language' => $russian->getId(),
                        'languageCredential' => 2,
                    ],
                ],
            ],
            'csrf' => $token,
        ];
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($data)
        );
        $this->dispatch($url);
        $this->assertResponseStatusCode(200);
        $response = $this->getResponse()->getBody();
        $json = json_decode($response);
        $this->assertTrue(is_object($json));
        $this->assertEquals($json->status,'success');

        $count_after = $em->createQuery('SELECT COUNT(i.id) FROM InterpretersOffice\Entity\Interpreter i')
                ->getSingleScalarResult();

        $this->assertEquals($count_after, $count_before + 1);

        //echo "\nDEBUG: exiting ".__FUNCTION__."\n";
    }

    public function testIndexAction()
    {
        $this->dispatch('/admin/interpreters');
        //echo $this->getResponse()->getBody(); return;
        $this->assertResponseStatusCode(200);
    }

    public function testUpdateInterpreter()
    {

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

        $document = new Document($this->getResponse()->getBody(), Document::DOC_HTML);
        //$document->setStringDocument($html);
        $query = new Document\Query();

        //$results = $query->execute('#lastname',$document,  Document\Query::TYPE_CSS);
        //$query = new Query($this->getResponse()->getBody());
        $node1 = $query->execute('#lastname', $document, Document\Query::TYPE_CSS)->current();
        $lastname = $node1->attributes->getNamedItem('value')->nodeValue;
        $this->assertEquals('Mintz', $lastname);

        // should have one language (Spanish)
        $this->assertQueryCount('div.language-name', 1);
        $this->assertQueryContentRegex('div.language-name', '/Spanish/');

        // and it should have AO certification
        $nodeList = $query->execute('div.language-credential > select > option', $document, Document\Query::TYPE_CSS);
        /** @var \DOMElement $element */
        $element = null;
        foreach ($nodeList as $e) {
            //if ($e->getAttributeNode('selected')) {
            if ($e->nodeValue == 'AO') {
                $element = $e;
                break;
            }
        }
        //var_dump($element->ownerDocument->saveXML($element));
        $this->assertInstanceOf(\DOMElement::class, $element);
        $this->assertEquals($element->getAttributeNode('selected')->value, 'selected');
        //<option value="88" selected="selected">AO</option>
        $this->assertEquals($element->nodeValue,"AO");

        $russian = $em->getRepository('InterpretersOffice\Entity\Language')
                ->findOneBy(['name' => 'Russian']);
        $this->assertInstanceOf(Entity\Language::class, $russian);
        $spanish = $em->getRepository('InterpretersOffice\Entity\Language')
                ->findOneBy(['name' => 'Spanish']);

        $this->reset(true);

        $token = $this->getCsrfToken($url);
        $hat_id = $em->getRepository('InterpretersOffice\Entity\Hat')
                        ->findOneBy(['name' => 'staff court interpreter'])->getId();
        $data = [
            'interpreter' => [
                'lastname' => 'Mintz',
                'firstname' => 'David',
                'hat' => $hat_id,
                'email' => 'david@davidmintz.org',
                'active' => 1,
                'id' => $id,
                'language-select' => 1,
                'interpreterLanguages' => [
                    [
                        'language' => $spanish->getId(),
                        'languageCredential' => "1",
                    ],
                    [
                        'language' => $russian->getId(),
                        'languageCredential' => '2',
                    ],
                ],
            ],
            'csrf' => $token,
        ];
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($data)
        );
        $this->dispatch($url);
        //$this->assertRedirect();
        //$this->assertRedirectTo('/admin/interpreters');
        $this->assertResponseStatusCode(200);
        $response = $this->getResponse()->getBody();
        $json = json_decode($response);
        $this->assertTrue(is_object($json));
        $this->assertEquals($json->status,'success');
        // load the form again
        $this->reset(true);
        $this->dispatch($url);

        //there should now be two languages
        $this->assertQueryCount('div.language-name', 2);
        // ...one of which is Russian
        $selector = '#language-' . $russian->getId();
        $this->assertQuery($selector);
        $this->assertQueryContentRegex($selector, '/Russian/');

        // now take it out
        unset($data['interpreter']['interpreterLanguages'][1]);
        // PLEASE do not forget this.
        // @todo:  make sure CSRF error thing is in the damn viewscript!

        $this->reset(true);
        $data['csrf'] = $this->getCsrfToken($url);
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($data)
        );
        $this->dispatch($url);
        $this->assertResponseStatusCode(200);
        $response = $this->getResponse()->getBody();
        $json = json_decode($response);
        $this->assertTrue(is_object($json));
        $this->assertEquals($json->status,'success');

        // load the form again
        $this->reset(true);
        $this->dispatch($url);

        // there should now be one language again
        $this->assertQueryCount('div.language-name', 1);
        $this->assertQueryContentRegex('div.language-name', '/Spanish/');
    }
}
