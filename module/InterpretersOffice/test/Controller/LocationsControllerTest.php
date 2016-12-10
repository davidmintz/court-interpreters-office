<?php

/** module/Application/test/Controller/LocationsControllerTest.php */

namespace ApplicationTest\Controller;

use ApplicationTest\AbstractControllerTest;
use InterpretersOffice\Admin\Controller\LocationsController;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;
use Zend\Stdlib\Parameters;
use Zend\Dom\Query;
use InterpretersOffice\Entity;

/**
 * test locations controller.
 *
 * @todo test the form input validation rules
 */
class LocationsControllerTest extends AbstractControllerTest
{
    public function setUp()
    {
        parent::setUp();
        $fixtureExecutor = FixtureManager::getFixtureExecutor();
        $fixtureExecutor->execute([
            new DataFixture\LocationLoader(),
            new DataFixture\MinimalUserLoader(),
        ]);
        $this->login('susie', 'boink');
    }

    /**
     * tests that we can add a new courtroom through the form.
     *
     * @return Entity\Courtroom
     */
    public function testAddCourtroom()
    {
        $this->dispatch('/admin/locations/add');
        $this->assertModuleName('interpretersoffice');
        $this->assertControllerName(LocationsController::class); // as specified in router's controller name alias
        $this->assertControllerClass('LocationsController');
        $this->assertMatchedRouteName('locations/add');
        $this->assertResponseStatusCode(200);
        $this->assertQuery('form');
        $this->assertQuery('#name');
        $this->assertQuery('#type');
        $this->assertQuery('#parentLocation');
        $this->assertQueryCount('input[name="active"]', 2);
        $this->assertQuery('textarea[name="comments"]');

        $em = FixtureManager::getEntityManager();
        $parent = $em->getRepository('InterpretersOffice\Entity\Location')
                ->findOneBy(['name' => '500 Pearl']);

        $type = $em->getRepository('InterpretersOffice\Entity\LocationType')
                ->findOneBy(['type' => 'courtroom']);

        $data = [
            'name' => '29F', // twilight zone
            'parentLocation' => $parent->getId(),
            'type' => $type->getId(),
            'comments' => 'shit is real',
            'active' => 1,
        ];

        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($data)
        );
        $this->dispatch('/admin/locations/add');
        $this->assertRedirect();
        $this->assertRedirectTo('/admin/locations');
        $courtroom = $em->getRepository('InterpretersOffice\Entity\Location')
                ->findOneBy(['name' => '29F']);
        $this->assertInstanceOf(Entity\Location::class, $courtroom);

        return $courtroom;
    }

    /**
     * @depends testAddCourtroom
     *
     * @param Entity\Location $courtroom
     */
    public function testUpdateCourtroom($courtroom)
    {
        $em = FixtureManager::getEntityManager();
        $courtroom->setType($em->getRepository('InterpretersOffice\Entity\LocationType')
                ->findOneBy(['type' => 'courtroom']));
        $courtroom->setParentLocation($em->getRepository('InterpretersOffice\Entity\Location')
                ->findOneBy(['name' => '500 Pearl']));
        $em->persist($courtroom);
        $em->flush();

        $url = '/admin/locations/edit/'.$courtroom->getId();

        $this->dispatch($url);
        $this->assertQuery('form');
        $this->assertQuery('#name');
        $this->assertQuery('#type');
        $this->assertQuery('#parentLocation');
        $this->assertQueryCount('input[name="active"]', 2);
        $this->assertQuery('textarea[name="comments"]');

        $query = new Query($this->getResponse()->getBody());
        $element = $query->execute('#name')->current();
        $elementValue = $element->attributes->getNamedItem('value')->nodeValue;
        $this->assertEquals('29F', $elementValue);

        $textarea = $query->execute('textarea[name="comments"]')->current();
        $this->assertEquals('shit is real', $textarea->nodeValue);

        $before = $textarea->nodeValue;

        $data = [
            'name' => '29F',
            'parentLocation' => $courtroom->getParentLocation()->getId(),
            'type' => $courtroom->getType()->getId(),
            'comments' => 'shit is truly real',
            'active' => 1,
            'id' => $courtroom->getId(),
        ];
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($data)
        );
        $this->dispatch($url);
        $this->assertRedirect();
        $this->assertRedirectTo('/admin/locations');

        $em->refresh($courtroom);

        $this->assertNotEquals($before, $courtroom->getComments());
    }

    public function testCourtroomFormValidation()
    {

        $em = FixtureManager::getEntityManager();
        $courtroom_type = $em->getRepository('InterpretersOffice\Entity\LocationType')
                ->findOneBy(['type' => 'courtroom']);
        
        // try adding a courtroom with no parent
        $data = [
            'name' => '29F',
            'parentLocation' => '',
            'type' => $courtroom_type ->getId(),
            'comments' => '',
            'active' => 1,
            //'id' => $courtroom->getId(),
        ];
         $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($data)
        );
        $this->dispatch('/admin/locations/add');
        $this->assertResponseStatusCode(200);
        $this->assertNotRedirect();
        $this->assertQuery('.validation-error');
        $this->assertQueryCount('.validation-error',1);
        $this->assertQueryContentRegex('.validation-error','/location has to have a parent/');
        //$query = new Query($this->getResponse()->getBody());
        //e$element = $query->execute('.validation-error')->current();
        
        // to be continued
    }
}
