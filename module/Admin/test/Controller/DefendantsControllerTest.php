<?php
namespace ApplicationTest\Controller;

use ApplicationTest\AbstractControllerTest;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;

use InterpretersOffice\Entity;
use InterpretersOffice\Entity\Repository\DefendantNameRepository;
use Zend\Stdlib\Parameters;

use Zend\Dom;

class DefendantsControllerTest extends AbstractControllerTest
{

    /** @var Entity\Repository\DefendantNameRepository $repository */
    protected $repository;

    public function setUp()
    {
        parent::setUp();
        FixtureManager::dataSetup([  new DataFixture\DefendantEventLoader(),]);
        $fixtureExecutor = FixtureManager::getFixtureExecutor();
        $this->repository = FixtureManager::getEntityManager()
            ->getRepository(Entity\DefendantName::class);
        $container = $this->getApplicationServiceLocator();
        //$container->get('log');
        $this->repository->setLogger($container->get('log'));
        //$fixtureExecutor->execute(
        //    [  new DataFixture\DefendantEventLoader(),]
        //);

        //$this->login('susie', 'boink');$data = $this->repository->findDocketAndJudges($rodriguez_jose->getId());
        //$this->reset(true);
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
            [events_affected] => Array
                (
                    [0] => 7
                    [1] => 8
                    [2] => 9
                    [3] => 10
                    [4] => 11
                )

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
        $eusebio = $this->repository->findOneBy(['given_names'=>'Eusebio Morales']);
        $contexts = $this->repository->findDocketAndJudges($eusebio);
        // sanity check
        $this->assertTrue(is_array($contexts));
        $this->assertEquals(2,$contexts[0]['events']);
        //print_r($contexts);
    }

}
