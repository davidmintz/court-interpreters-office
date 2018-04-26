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

        //$this->login('susie', 'boink');
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

    public function __testPartialUpdateWithoutDeftNameUpdate()
    {
        $rodriguez_jose = $this->repository->findOneBy(['surnames'=>'Rodriguez','given_names'=>'Jose']);
        $data = $this->repository->findDocketAndJudges($rodriguez_jose->getId());
        $result = $this->repository->updateDefendantEvents($rodriguez_jose,[json_encode($data[0])]);
        print_r($result);

    }

}
