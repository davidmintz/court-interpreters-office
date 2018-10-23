<?php
/**
 * module/Requests/test/Controller/RequestsIndexControllerTest.php
 *
 */

namespace ApplicationTest\Controller;

use InterpretersOffice\Requests\Controller\RequestsIndexController;
use ApplicationTest\AbstractControllerTest;

use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;
use Zend\Stdlib\Parameters;

/**
 * unit test for InterpretersOffice\Requests module's main controller
 */
class RequestsIndexControllerTest extends AbstractControllerTest
{
    public function setUp()
    {
        parent::setUp();
        $fixtureExecutor = FixtureManager::getFixtureExecutor();
        $fixtureExecutor->execute(
            [
                new DataFixture\HatLoader(),
                new DataFixture\DefendantLoader(),
                new DataFixture\LocationLoader(),
                new DataFixture\JudgeLoader(),
                new DataFixture\LanguageLoader(),
                new DataFixture\EventTypeLoader(),
                new DataFixture\InterpreterLoader(),
                new DataFixture\UserLoader(),
            ]
        );
        // $container = $this->getApplicationServiceLocator();
        // $em = $container->get("entity-manager");
        // $listener = $container->get('InterpretersOffice\Entity\Listener\UpdateListener');
        // $resolver = $em->getConfiguration()->getEntityListenerResolver();
        // $resolver->register($listener);
        // $resolver->register($container->get('InterpretersOffice\Requests\Entity\Listener\RequestEntityListener'));


    }
    public function testIndexCannotBeAccessedWithoutLogin()
    {
        $this->dispatch('/requests');
        $this->assertRedirect();
    }

    public function __testLoginSanity()
    {
        $this->login('jane_zorkendoofer@nysd.uscourts.gov','gack!');
        $this->reset(true);
        $this->dispatch('/requests');
        $this->assertResponseStatusCode(200);
    }

    public function getDummyRequest()
    {

        $em = FixtureManager::getEntityManager();


        $dql = "SELECT j.id FROM InterpretersOffice\Entity\User u JOIN u.judges j JOIN u.person p
        WHERE p.email = 'jane_zorkendoofer@nysd.uscourts.gov'";

        $judge = $em->createQuery($dql)->getResult()[0]['id'];
        $spanish = $em->createQuery(
            "SELECT l.id FROM InterpretersOffice\Entity\Language l
                WHERE l.name = 'Spanish'"
            )->getSingleScalarResult();
        $conference = $em->createQuery(
            "SELECT t.id FROM InterpretersOffice\Entity\EventType t
                WHERE t.name = 'conference'"
            )->getSingleScalarResult();
        $location = $em->find('InterpretersOffice\Entity\Judge',$judge)
            ->getDefaultLocation()->getId();

        return [
                'judge' => $judge,
                'docket' => '2018-CR-1234',
                'language' => $spanish,
                'eventType' => $conference,
                'location'  => $location,
                'date' => (new \DateTime('next Tuesday +1 week'))->format('m/d/Y'),
                'time' => (new \DateTime("today 10:00 am"))->format('g:i a'),
                'comments' => 'boink gack babble babble',
                'id' => '',
            ];
    }

    public function testLoadCreatePage()
    {
        $this->login('jane_zorkendoofer@nysd.uscourts.gov','gack!');
        $this->reset(true);
        $this->dispatch('/requests/create');
        $this->assertResponseStatusCode(200);
        $this->assertQuery("form");
        $this->assertQuery("#date");
        $this->assertQuery("#time");


    }

    public function testCreate()
    {

        $em = FixtureManager::getEntityManager();
        $before = $em->createQuery('SELECT COUNT(r.id) FROM InterpretersOffice\Requests\Entity\Request r')
            ->getSingleScalarResult();
        $data = $this->getDummyRequest();
        $this->login('jane_zorkendoofer@nysd.uscourts.gov','gack!');
        $this->reset(true);
        $token = $this->getCsrfToken('/requests/create');
        $post = ['csrf' => $token,'request'=> $data];
        $this->reset(true);
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($post)
        );
        $this->dispatch('/requests/create');

        $this->assertResponseStatusCode(200);
        $after = $em->createQuery('SELECT COUNT(r.id) FROM InterpretersOffice\Requests\Entity\Request r')
           ->getSingleScalarResult();

        $this->assertTrue($after == $before + 1);

    }
}
