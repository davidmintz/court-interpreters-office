<?php

namespace ApplicationTest\Controller;

use InterpretersOffice\Requests\Controller\RequestsIndexController;
use ApplicationTest\AbstractControllerTest;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;
use Zend\Stdlib\Parameters;
use InterpretersOffice\Admin\Notes\Entity\MOTD;

class NotesControllerTest extends AbstractControllerTest
{
    public function setUp()
    {
        parent::setUp();
        $fixtureExecutor = FixtureManager::getFixtureExecutor();
        $fixtureExecutor->execute(
            [
                // new DataFixture\DefendantLoader(),
                // new DataFixture\EventTypeLoader(),
                new DataFixture\HatLoader(),
                new DataFixture\LocationLoader(),
                new DataFixture\JudgeLoader(),
                new DataFixture\LanguageLoader(),
                new DataFixture\InterpreterLoader(),
                new DataFixture\UserLoader(),
            ]
        );
    }

    public function tearDown()
    {
        $em = FixtureManager::getEntityManager();
        $motd = $em->createQuery("SELECT n FROM InterpretersOffice\Admin\Notes\Entity\MOTD n ORDER BY n.date DESC" )
           ->getOneOrNullResult();
        if ($motd) {
            $em->remove($motd);
            $em->flush();
        }
    }

    public function testGetMethodWorksIfUserIsLoggedIn()
    {
        $this->login('david','boink');
        $this->reset(true);
        $this->dispatch('/admin/notes/date/'.date('Y-m-d').'/motd');
        $this->assertResponseStatusCode(200);
    }

    public function testGetMethodFailsIfUserIsNotLoggedIn()
    {
        $this->dispatch('/admin/notes/date/'.date('Y-m-d').'/motd');
        $this->assertNotResponseStatusCode(200);
    }

    public function testGetByDateMethodWorks()
    {
        $this->login('david','boink');
        $this->reset(true);
        $this->dispatch('/admin/notes/date/2019-09-12/motd');
        $this->assertResponseStatusCode(200);
    }

    public function testLoadFormToCreateMOTD()
    {
        $this->login('david','boink');
        $this->reset(true);
        $when = new \DateTime('next Tuesday');
        $date_str = $when->format('Y-m-d');
        $this->dispatch('/admin/notes/create/motd/'.$date_str);
        $this->assertResponseStatusCode(200);
        $this->assertQueryCount('form textarea',1);

    }

    public function testCreateMOTD()
    {
        $em = FixtureManager::getEntityManager();
        $count_before = $em->getRepository(MOTD::class)->count([]);
        $when = new \DateTime('next Tuesday');
        $date_str = $when->format('Y-m-d');

        $url = "/admin/notes/create/motd/$date_str";
        $this->login('david', 'boink');
        $this->reset(true);
        $token = $this->getCsrfToken($url, 'csrf');
        $content = 'Here is your test message, hope you enjoy it';
        $this->getRequest()
            ->setMethod('POST')->setPost(
            new Parameters([
                'content' => $content,
                'type' => 'motd',
                'date' => $date_str,
                'id' => '',
                'csrf' => $token,
            ])
        )->getHeaders()->addHeaderLine('X-Requested-With','XMLHttpRequest');
        $this->dispatch('/admin/notes/create/motd');
        $this->assertResponseStatusCode(200);
        $this->assertResponseHeaderRegex('Content-type', '|application/json|');
        $body = $this->getResponse()->getBody();
        $data = json_decode($body);
        $this->assertIsObject($data);
        $this->assertEquals('success',$data->status);
        $this->assertIsObject($data->motd);
        $this->assertObjectHasAttribute('id', $data->motd);
        $this->assertObjectHasAttribute('created_by', $data->motd);
        $this->assertEquals('david',$data->motd->created_by);
        $this->assertIsString($data->motd->content);
        $this->assertEquals($content,$data->motd->content);
        $count_after = $em->getRepository(MOTD::class)->count([]);
        $this->assertEquals($count_after, $count_before + 1);

        return $data->motd; // for a possible future @depends etc
    }


}
