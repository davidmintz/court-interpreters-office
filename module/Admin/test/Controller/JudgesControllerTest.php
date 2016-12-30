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

    // to be continued
}
