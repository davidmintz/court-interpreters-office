<?php
/**
 * module/Admin/test/Controller/PeopleControllerTest.php.
 */

namespace ApplicationTest\Controller;

//use InterpretersOffice\Admin\Controller\JudgesController;
use ApplicationTest\AbstractControllerTest;
use Zend\Stdlib\Parameters;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;

/**
 * JudgesControllerTest.
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

       ;
    }

    public function testIndexAction()
    {
        $this->login('susie', 'boink');
        $this->reset(true);
        $this->dispatch('/admin/judges');
        $this->assertResponseStatusCode(200);

        $count = FixtureManager::getEntityManager()
            ->createQuery('SELECT COUNT(j.id) FROM InterpretersOffice\Entity\Judge j')
            ->getSingleScalarResult();

        $this->assertQueryContentRegex('title', '/^judges/');
        //$this->dumpResponse();return;
        $this->assertQuery('div#USDJ');
        $this->assertQueryCount('tr', $count);
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
        $this->login('susie', 'boink');
        $this->reset(true);
        $this->dispatch('/admin/judges/add');
        $data = [
            'judge' => [
                'lastname' => 'Henklebaum',
                'firstname' => 'Jane',
                'middlename' => 'B.',
                'flavor' => $flavor->getId(),
                'default_location' => $courtroom->getId(),
                'active' => 1,
                'email' => '',
            ],
            'csrf' => $this->getCsrfToken('/admin/judges/add'),
        ];
        $this->getRequest()->setMethod('POST')->setPost(new Parameters($data));
        $this->dispatch('/admin/judges/add');
        //$this->getResponse()->getBody(); //return;
        $this->assertRedirect();
        $this->assertRedirectTo('/admin/judges');

        $this->assertEquals($count + 1,
            $entityManager
            ->createQuery('SELECT COUNT(j.id) FROM InterpretersOffice\Entity\Judge j')
            ->getSingleScalarResult());
    }

    /**
     * tests that everything looks ok in the add-judge page even if there are
     * no locations in the database.
     */
    public function testAddJudgePageNotBlowUpWhenThereAreNoLocations()
    {
        $entityManager = $this->getApplicationServiceLocator()->get('entity-manager');

        // set all the judge default locations to null
        $dql = 'UPDATE InterpretersOffice\Entity\Judge j SET j.defaultLocation = NULL';
        $query = $entityManager->createQuery($dql);
        $query->getResult();
        // delete the location entities having a parent
        $entityManager->createQuery(
            'DELETE InterpretersOffice\Entity\Location l where l.parentLocation IS NOT NULL')
            ->getResult();
        // delete the rest
        $entityManager
                ->createQuery('DELETE InterpretersOffice\Entity\Location l where l.parentLocation IS NULL')
                ->getResult();
        $this->login('susie', 'boink');
        $this->reset(true);
        // load the "add" page
        $this->dispatch('/admin/judges/add');
        $this->assertResponseStatusCode(200);

        $this->assertQuery('form');
        $this->assertQuery('#lastname');
        $this->assertQuery('#firstname');
        $this->assertQuery('#middlename');
        $this->assertQuery('#courthouse option');

       // echo $this->getResponse()->getBody();

        $this->assertQueryCount('#courthouse option', 1);
        $this->assertQuery('#courtroom option');
        $this->assertQueryCount('#courtroom option', 1);

        $flavor = $entityManager->getRepository('InterpretersOffice\Entity\JudgeFlavor')
            ->findOneBy(['flavor' => 'USDJ']);

        $data = [
            'judge' => [
                'lastname' => 'Henklebaum',
                'firstname' => 'Jane',
                'middlename' => 'B.',
                'flavor' => $flavor->getId(),
                'default_location' => '',
                'active' => 1,
                'email' => '',
            ],
            'csrf' => $this->getCsrfToken('/admin/judges/add'),
        ];
        $this->getRequest()->setMethod('POST')->setPost(new Parameters($data));
        $this->dispatch('/admin/judges/add');
        //echo $this->getResponse()->getBody(); //return;
        $this->assertRedirect();
        $this->assertRedirectTo('/admin/judges');
    }
}
