<?php

namespace ApplicationTest\Controller;

use InterpretersOffice\Requests\Controller\RequestsIndexController;
use ApplicationTest\AbstractControllerTest;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;
use Laminas\Stdlib\Parameters;
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
                // new DataFixture\NoteLoader(),
            ]
        );
        // echo "leaving ". __METHOD__ . "\n";
    }

    public function tearDown()
    {
        $em = FixtureManager::getEntityManager();
        $em->createQuery('DELETE InterpretersOffice\Admin\Notes\Entity\MOTD m')->getResult();
        $em->createQuery('DELETE InterpretersOffice\Admin\Notes\Entity\MOTW m')->getResult();
        // $motd = $em->createQuery("SELECT n FROM InterpretersOffice\Admin\Notes\Entity\MOTD n ORDER BY n.date DESC" )
        //    ->getOneOrNullResult();
        // if ($motd) {
        //     $em->remove($motd);
        $em->flush();
        // }
    }

    public function testGetMethodWorksIfUserIsLoggedIn()
    {
        $this->login('david','boink');
        $this->reset(true);
        $this->dispatch('/admin/notes/date/'.date('Y-m-d').'/motd');
        $this->assertResponseStatusCode(200);
        // echo "leaving ". __METHOD__ . "\n";
    }

    public function testGetMethodFailsIfUserIsNotLoggedIn()
    {
        $this->dispatch('/admin/notes/date/'.date('Y-m-d').'/motd');
        $this->assertNotResponseStatusCode(200);
        // echo "leaving ". __METHOD__ . "\n";
    }

    public function testGetByDateMethodWorks()
    {
        $em = FixtureManager::getEntityManager();
        $count = $em->getRepository(MOTD::class)->count([]);
        // echo "\nthe FUCKING count = $count\n";
        // $sample = $em->createQuery('SELECT m FROM InterpretersOffice\Admin\Notes\Entity\MOTD m ORDER BY m.date')->getResult()[0];
        // print_r($sample->JsonSerialize());
        $this->login('david','boink');
        $this->reset(true);
        $date = date('Y-m-d');
        // $date = $sample->getDate()->format('Y-m-d');
        // echo "\nCHECKING: $date at /admin/notes/date/$date/motd\n";
        // $this->assertTrue(strlen($sample->getContent()) > 0);
        $this->dispatch("/admin/notes/date/$date/motd");
        // $this->dumpResponse();
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
        $this->assertQuery('form textarea');

    }

    public function testCreateMOTD()
    {
        $em = FixtureManager::getEntityManager();
        $count_before = $em->getRepository(MOTD::class)->count([]);
        $when = new \DateTime();
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
        // print_r($data);
        return $data->motd; // for a possible future @depends etc
    }


}
