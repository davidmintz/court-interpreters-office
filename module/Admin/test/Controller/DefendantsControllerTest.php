<?php
namespace ApplicationTest\Controller;

use ApplicationTest\AbstractControllerTest;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;

use InterpretersOffice\Entity;
use InterpretersOffice\Entity\Repository\DefendantRepository;
use InterpretersOffice\Entity\Defendant;
use Laminas\Stdlib\Parameters;

use Laminas\Dom;
use Laminas\Log\Writer\Noop;
use Doctrine\ORM\EntityManager;

use InterpretersOffice\Admin\Service\DefendantNameService;

class DefendantsControllerTest extends AbstractControllerTest
{

    /** @var Entity\Repository\DefendantRepository $repository */
    protected $repository;

    /** @var \Doctrine\ORM\EntityManager $em */
    private $em;

    /** @var DefendantNameService $service */
    protected $service;

    //

    public function setUp()
    {
        parent::setUp();
        FixtureManager::dataSetup([  new DataFixture\DefendantEventLoader(),]);
        $fixtureExecutor = FixtureManager::getFixtureExecutor();
        $this->em = FixtureManager::getEntityManager();
        $this->repository = $this->em->getRepository(Entity\Defendant::class);
        //$this->repository
        //$container = $this->getApplicationServiceLocator();
        $log = new \Laminas\Log\Logger();
        $log->addWriter(new \Laminas\Log\Writer\Noop());
        //$this->repository->setLogger($container->get('log'));
        $this->repository->setLogger($log);

        $this->service = new DefendantNameService($this->em);
        //$fixtureExecutor->execute(
        //    [  new DataFixture\DefendantEventLoader(),]
        //);

        //$this->login('susie', 'boink');$data = $this->repository->findDocketAndJudges($rodriguez_jose->getId());
        //$this->reset(true);
    }

    /**
     * tests findDocketAndJudges
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
        $rodriguez_jose = $this->repository->findOneBy(['surnames' => 'Rodriguez','given_names' => 'Jose']);
        $id = $rodriguez_jose->getId();
        $data = $this->repository->findDocketAndJudges($id);
        $this->assertTrue(is_array($data));
        $this->assertTrue($data[0]['events'] == 5);
        $this->assertTrue($data[1]['events'] == 5);
        $judges = array_column($data, 'judge');
        $this->assertTrue(count($judges) == 2);
        $this->assertTrue(in_array('Dinklesnort', $judges));
        $this->assertTrue(in_array('Noobieheimer', $judges));
    }

    public function testPartialUpdateWithoutDeftNameUpdateAndNoExistingMatch()
    {
        /** @var Entity\Defendant $rodriguez_jose */
        $rodriguez_jose = $this->repository->findOneBy(['surnames' => 'Rodriguez','given_names' => 'Jose']);
        // sanity
        $this->assertTrue(is_object($rodriguez_jose));
       // $rodriguez_jose->setFirstname("José Improbable");
        $data = $this->repository->findDocketAndJudges($rodriguez_jose->getId());
        //printf("\n%s\n",'data for jose rodriguez'); print_r($data);
        // update only for Dinklesnort
        // // they are ordered by docket followed by judge, so
        // Dinklesnort 15-CR-... will be first
        list($dinklesnort_events, $noobieheimer_events) = $data;
        $data['contexts'] = [json_encode($dinklesnort_events)];
        $data['given_names'] = "José Improbable";
        $data['surnames']  = 'Rodriguez';
        $data['id'] = $rodriguez_jose->getId();
        $result = $this->service->update($rodriguez_jose,$data);
        $this->assertTrue(is_array($result));

        // five deft_event rows should have been updated
        $this->assertTrue(is_int($result['deft_events_updated']));
        $this->assertEquals(5, $result['deft_events_updated']);
        // a new name should have been inserted
        $this->assertTrue(key_exists('deft_name_inserted',$result));
        $this->assertEquals(1,$result['deft_name_inserted']);
        $this->assertTrue(key_exists('entity', $result));
        $this->assertTrue(key_exists('id', $result['entity']));
        $id = $result['entity']['id'];
        // check that other events are unchanged
        //$this->repository->deleteCache();
        $data = $this->repository->findDocketAndJudges($rodriguez_jose);
        $this->assertEquals(1, count($data));
        $this->assertEquals($noobieheimer_events, $data[0]);

        // new guy's events should look like former guy's Dinklesnort
        $data = $this->repository->findDocketAndJudges($id);
        $this->assertEquals(1, count($data));
        $this->assertEquals($dinklesnort_events, $data[0]);
    }

    public function testGlobalNameUpdateWithNoExistingMatch()
    {
        //['Rodríguez', 'Eusebio Morales']
        /** @var Entity\Defendant $eusebio */
        $eusebio = $this->repository->findOneBy(['given_names' => 'Eusebio Morales']);
        $id = $eusebio->getId();
        $contexts = $this->repository->findDocketAndJudges($eusebio);
        // sanity check
        $this->assertTrue(is_array($contexts));
        $this->assertEquals(1, count($contexts));
        $this->assertEquals(2, $contexts[0]['events']);
        $data = ['given_names'=>'Eusebio','surnames'=>'Rodríguez Morales','id'=>$eusebio->getId()];
        $data['contexts'] = [json_encode($contexts[0])];
        $result = $this->service->update($eusebio,$data);
        
        // old version should be gone...
        $null = $this->repository->findOneBy(['given_names' => 'Eusebio Morales']);
        $this->assertNull($null);

        $eusebio_redux = $this->repository->find($id);
        $this->assertEquals('Rodríguez Morales, Eusebio', (string)$eusebio_redux);
    }
    public function testGlobalNameUpdateWithExistingMatchUsingExisting()
    {
        /** @var Entity\Defendant $rodriguez_jose */
        $rodriguez_jose = $this->repository->findOneBy(['surnames' => 'Rodriguez','given_names' => 'Jose']);
        $contexts = $this->repository->findDocketAndJudges($rodriguez_jose);
        
        $rodriguez_jose->setSurnames('Rodriguez Medina')->setGivenNames('José');
        $match = $this->service->findDuplicate($rodriguez_jose);
        // issue: SQLite3 does not enforce duplicate entry constraints or
        // compare strings the same way MySQL does, or at least not by default
        // printf("\nmatch: %s\n", gettype($match));
        //$shit = $this->getApplicationServiceLocator()->get('entity-manager');
        //$db = $this->em->getConnection()->getDatabase();
        //printf("\nour shit is: %s\n)",$db);
        $data = $rodriguez_jose->toArray();
        $data['contexts'] =  [json_encode($contexts[0])];
        $data['duplicate_resolution'] = $this->service::USE_EXISTING_DUPLICATE;
        $result = $this->service->update($rodriguez_jose,$data);
        
        //print_r($result);
        $this->assertTrue(is_array($result));
        // $bullshit = $this->em->createQuery(
        //     'SELECT d.given_names, d.surnames, d.id FROM InterpretersOffice\Entity\Defendant d '
        //     . 'WHERE d.surnames LIKE :surnames  AND d.given_names LIKE :given_names'
        // )->useResultCache(false)->setParameters(['surnames'=>'rod%','given_names'=> 'Jo%'])->getResult();
        // print_r($bullshit);
        // printf("database? %s\n",$this->em->getConnection()->getDriver()->getName());
    }
}
