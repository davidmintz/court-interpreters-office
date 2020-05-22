<?php

namespace ApplicationTest\Controller;

use ApplicationTest\AbstractControllerTest;
use ApplicationTest\FixtureManager;
use ApplicationTest\DummyDataTrait;
use InterpretersOffice\Entity;
use Laminas\Stdlib\Parameters;

use Laminas\Dom;

class EmailControllerTest extends AbstractControllerTest
{
    use DummyDataTrait;

    public function setUp()
    {
        parent::setUp();
        FixtureManager::dataSetup();

        $this->login('david', 'boink');
        $this->reset(true);
    }

    public function testSendEMailConfirmationTurnsOnConfirmationFlag()
    {
        $em = FixtureManager::getEntityManager();
        $event = $em->getRepository(Entity\Event::class)->findOneBy(['docket'=>\ApplicationTest\DataFixture\EventLoader::DUMMY_DOCKET]);
        $id = $event->getId();
        $interpreter = $event->getInterpreters()[0];
        $email = $interpreter->getEmail();
        $name = $interpreter->getFullName();
        // var_dump("$name <$email>");
        $this->login('david', 'boink');
        $this->reset(true);
        $this->dispatch("/admin/schedule/view/$id");
        $body = $this->getResponse()->getBody();
        $doc = new Dom\Query($body);
        $node = $doc->execute("#btn-delete")->current();
        $csrf = $node->getAttribute("data-csrf");
      
        $details = [];
        $details['category'] = $doc->execute(".event-details")->current()->getAttribute("data-event_category");
        foreach(['language','date','time','docket','event_type','judge','submitter','location'] as $prop) {
            $details[$prop] = trim($doc->execute(".{$prop}")->current()->nodeValue);
        }
        $details['time'] = preg_replace('/(.+ a[pm]).*/',"$1",$details['time']);              
        preg_match('|<div class=".*defendants">(.+)</div>|',$body,$matches);
        $details['defendants'] = trim($matches[1]);
        preg_match('|<div class=".*interpreters">(.+)</div>|',$body,$matches);
        $details['interpreters'] = trim($matches[1]);        
        $this->reset(true);
        // // $this->dumpResponse();
        $this->login('david', 'boink');
        $this->reset(true);
        $this->dispatch('/admin/email/event','POST',[
            'csrf' => $csrf,
            'message' => [
                'subject' => 'assignment confirmed',
                'to' => [
                   ['name'=> $name,'email'=>$email,'id'=>$interpreter->getId()]
                ],
                'template_hint'=>'confirmation',
                'event_id' => $id,
                'event_details'=> $details,
            ],
        ]);
        $this->assertResponseStatusCode(200);
        $string = $this->getResponse()->getBody();
        $response = json_decode($string);

        $this->assertTrue(is_object($response));

        // now see if confirmation attrib is true

        $em->refresh($event);
        $ie = $event->getInterpreterEvents()[0];        
        $this->assertTrue($ie->getSentConfirmationEmail());
    }  
}