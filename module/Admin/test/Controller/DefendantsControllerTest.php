<?php
namespace ApplicationTest\Controller;

use ApplicationTest\AbstractControllerTest;
use ApplicationTest\FakeAuth;

use ApplicationTest\Bootstrap;
use ApplicationTest\DataFixture;

use InterpretersOffice\Entity;
use InterpretersOffice\Entity\Repository\DefendantNameRepository;
use InterpretersOffice\Entity\DefendantName;
use Zend\Stdlib\Parameters;

use Zend\Dom;

class DefendantsControllerTest extends AbstractControllerTest
{

    /** @var Entity\Repository\DefendantNameRepository $repository */
    protected $repository;

    public function setUp()
    {
        parent::setUp();

        $this->repository = Bootstrap::getEntityManager()
            ->getRepository(Entity\DefendantName::class);
        $container = $this->getApplicationServiceLocator();
        $this->repository->setLogger($container->get('log'));
        Bootstrap::load(
            [
                new DataFixture\LocationTypes(),
                new DataFixture\Locations(),
                new DataFixture\Languages(),
                new DataFixture\DefendantNames(),
                new DataFixture\EventTypeCategories(),
                new DataFixture\EventTypes(),
                new DataFixture\Roles(),
                new DataFixture\Hats(),
                new DataFixture\Judges(),
                new DataFixture\Interpreters(),
                new DataFixture\Users(),
                new DataFixture\Events(),
                new DataFixture\DefendantsEvents(),
            ]
        );
    }

    /**
     * test findDocketAndJudges
     *
     * ensures there are two docket-contexts for defendant name
     * "Rodriguez, Jose", each with 5 events. sort of a sanity check of data
     * loader as well.
     * @return void
     */
    public function testFindDocketAndJudges()
    {
        $this->login('david', 'boink');
        $this->reset(true);
        $rodriguez_jose = $this->repository->findOneBy(['surnames'=>'Rodriguez','given_names'=>'Jose']);
        $id = $rodriguez_jose->getId();
        $data = $this->repository->findDocketAndJudges($id);
        $this->assertTrue(is_array($data));
        $this->assertTrue($data[0]['events']==5);
        $this->assertTrue($data[1]['events']==5);
        $judges = array_column($data, 'judge');
        $this->assertTrue(count($judges)==2);
        $this->assertTrue(in_array('Dinklesnort',$judges));
        $this->assertTrue(in_array('Noobieheimer',$judges));

    }

    public function testPartialUpdateWithoutDeftNameUpdateAndNoExistingMatch()
    {
        /** @var Entity\DefendantName $rodriguez_jose */
        $rodriguez_jose = $this->repository->findOneBy(['surnames'=>'Rodriguez','given_names'=>'Jose']);
        // sanity
        $this->assertTrue(is_object($rodriguez_jose));
        $rodriguez_jose->setFirstname("José Improbable");
        $data = $this->repository->findDocketAndJudges($rodriguez_jose->getId());
        //printf("\n%s\n",'data for jose rodriguez'); print_r($data);
        // update only for Dinklesnort
        // // they are ordered by docket followed by judge, so
        // Dinklesnort 15-CR-... will be first
        list($dinklesnort_events, $noobieheimer_events) = $data;

        $result = $this->repository->updateDefendantEvents($rodriguez_jose,
            [json_encode($dinklesnort_events)]);
        $this->assertTrue(is_array($result));
        // 5 events should have been affected
        /* (
            [match] =>
            [update_type] => partial
            [events_affected] => Array [....]
            [status] => success
            [deft_events_updated] => 5
            [insert_id] => 13
        )
        */
        // five events should have been events_affected
        $this->assertTrue(is_array($result['events_affected']));
        $this->assertEquals(5,count($result['events_affected']));

        // a new name should have been inserted
        $this->assertTrue(key_exists('insert_id', $result));

        // check that other events are unchanged
        $this->repository->deleteCache();
        $data = $this->repository->findDocketAndJudges($rodriguez_jose);
        $this->assertEquals(1,count($data));
        $this->assertEquals($noobieheimer_events,$data[0]);

        // new guy's events should look like former guy's Dinklesnort
        $data = $this->repository->findDocketAndJudges($result['insert_id']);
        $this->assertEquals(1,count($data));
        $this->assertEquals($dinklesnort_events,$data[0]);

    }

    public function testGlobalNameUpdateWithNoExistingMatch()
    {
        //['Rodríguez', 'Eusebio Morales']
        /** @var Entity\DefendantName $eusebio */
        $eusebio = $this->repository->findOneBy(['given_names'=>'Eusebio Morales']);
        $id = $eusebio->getId();
        $contexts = $this->repository->findDocketAndJudges($eusebio);
        // sanity check
        $this->assertTrue(is_array($contexts));
        $this->assertEquals(1,count($contexts));
        $this->assertEquals(2,$contexts[0]['events']);
        $eusebio->setGivenNames("Eusebio")->setSurnames("Rodríguez Morales");

        $result = $this->repository->updateDefendantEvents(
            $eusebio,[json_encode($contexts[0])]);

        $this->repository->deleteCache();
        // NB: no events are considered to have been updated, because an element of
        // the DefendantNames collection has changed, but not the collection itself.

        // old version should be gone...
        $null = $this->repository->findOneBy(['given_names'=>'Eusebio Morales']);
        $this->assertNull($null);

        $eusebio_redux = $this->repository->find($id);
        $this->assertEquals('Rodríguez Morales, Eusebio',(string)$eusebio_redux);
    }
    public function testGlobalNameUpdateWithExistingMatchUsingExisting()
    {

        //$this->assertTrue($auth->hasIdentity());

        $container = $this->getApplicationServiceLocator();
        $objectManager = $container->get('entity-manager');//Bootstrap::getEntityManager();
        /** @var Entity\DefendantName $rodriguez_jose */
        $rodriguez_jose = $this->repository->findOneBy(['surnames'=>'Rodriguez','given_names'=>'Jose']);
        $contexts = $this->repository->findDocketAndJudges($rodriguez_jose);

        // already existing: ['Rodríguez Medina', 'José'],
        $rodriguez_jose->setSurnames('Rodriguez Medina')->setGivenNames('José');
        $match = $this->repository->findDuplicate($rodriguez_jose);
        // issue: SQLite3 does not enforce duplicate entry constraints or
        // compare strings the same way MySQL does, or at least not by default
        // and we were unable to figure out if there's away to make sqlite do
        // otherwise, so we switched our test database to mysql
        $auth = $container->get('auth');
        $listener = $container->get(Entity\Listener\EventEntityListener::class);
        $listener->setAuth($auth);
        $this->login('david', 'boink');
        $result = $this->repository->updateDefendantEvents(
            $rodriguez_jose,  [json_encode($contexts[0])],
            $match,'use_existing');
        $this->assertTrue(is_array($result));
        $this->assertEquals(5, $result['deft_events_updated']);
        printf("\nbullshit in %s at %d?\n",basename(__FILE__),__LINE__);
        print_r($result);
        //$this->assertTrue(is_int($result['deftname_replaced_by']));
    }
}
