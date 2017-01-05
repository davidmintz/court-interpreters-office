<?php
/**
 * module/Admin/test/Controller/PeopleControllerTest.php
 */
namespace ApplicationTest\Controller;

//use InterpretersOffice\Admin\Controller\JudgesController;
use ApplicationTest\AbstractControllerTest;
use Zend\Stdlib\Parameters;
use Zend\Dom\Query;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;
use InterpretersOffice\Entity;

/**
 * JudgesControllerTest
 */
class JudgesControllerTest extends AbstractControllerTest
{

    public function setUp()
    {

        parent::setUp();
        $fixtureExecutor = FixtureManager::getFixtureExecutor();
        $fixtureExecutor->execute(
            [
            new DataFixture\MinimalUserLoader(),
            new DataFixture\LocationLoader(),
            new DataFixture\JudgeLoader(),

            ]
        );

        $this->login('susie', 'boink');
    }

    public function testIndexAction()
    {
        $this->dispatch('/admin/judges');
        $this->assertResponseStatusCode(200);

        $count = FixtureManager::getEntityManager()
            ->createQuery('SELECT COUNT(j.id) FROM InterpretersOffice\Entity\Judge j')
            ->getSingleScalarResult();

        $this->assertQueryContentRegex('title', '/^judges/');
        $this->assertQuery('ul.list-group');
        $this->assertQueryCount('ul.list-group li', $count);
    }

    public function testAddAction()
    {
        $entityManager = FixtureManager::getEntityManager();
        $count = $entityManager
            ->createQuery('SELECT COUNT(j.id) FROM InterpretersOffice\Entity\Judge j')
            ->getSingleScalarResult();
        $courtroom = $entityManager->getRepository('InterpretersOffice\Entity\Location')
            ->findOneBy(['name' => '15C']);
        // sanity check
        $this->assertTrue($courtroom instanceof \InterpretersOffice\Entity\Location);
        $flavor = $entityManager->getRepository('InterpretersOffice\Entity\JudgeFlavor')
            ->findOneBy(['flavor' => 'USDJ']);
        // sanity check
        $this->assertTrue($flavor instanceof \InterpretersOffice\Entity\JudgeFlavor);
        $this->dispatch('/admin/judges/add');
        $data = [
            'judge' => [
                'lastname' => 'Henklebaum',
                'firstname' => 'Jane',
                'middlename' => 'B.',
                'flavor'  => $flavor->getId(),
                'default_location' => $courtroom->getId(),
                'active' => 1,
            ],
            'csrf' => $this->getCsrfToken('/admin/judges/add')            
        ];
        $this->getRequest()->setMethod('POST')->setPost(new Parameters($data));
        $this->dispatch('/admin/judges/add'); 
        //echo $this->getResponse()->getBody(); //return;   
        $this->assertRedirect();
        $this->assertRedirectTo('/admin/judges');

        $this->assertEquals($count + 1,
            $entityManager
            ->createQuery('SELECT COUNT(j.id) FROM InterpretersOffice\Entity\Judge j')
            ->getSingleScalarResult());
    
    }
     public function testAddJudgeDoesNotBlowUpWhenThereAreNoLocations()
     {

        // set all the judge default locations to null
        $dql = 'UPDATE ... ';
     }
}
