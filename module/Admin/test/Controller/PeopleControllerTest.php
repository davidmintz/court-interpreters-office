<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 *
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ApplicationTest\Controller;

use InterpretersOffice\Admin\Controller\PeopleController;
use ApplicationTest\AbstractControllerTest;
use Zend\Stdlib\Parameters;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;
use InterpretersOffice\Entity;

class PeopleControllerTest extends AbstractControllerTest
{
    public function setUp()
    {
        
        parent::setUp();
        $fixtureExecutor = FixtureManager::getFixtureExecutor();
        $fixtureExecutor->execute(
            [new DataFixture\MinimalUserLoader()]);
        
        $this->login('susie', 'boink');
    }

    public function testAccessPeopleAdminPage()
    {

        $this->dispatch('/admin/people');
        $this->assertResponseStatusCode(200);
        //$this->assertModuleName('interpretersofficeadmin');
        $this->assertControllerName(PeopleController::class); // as specified in router's controller name alias
        $this->assertControllerClass('PeopleController');
        $this->assertMatchedRouteName('people');        
    }

    public function testAdd()
    {
      
        // first try a GET to check the form
        $this->dispatch('/admin/people/add');
        $this->assertResponseStatusCode(200);
        $this->assertQuery("form");
        $this->assertQuery('input#lastname');
        $this->assertQuery('input#firstname');
        $this->assertQuery('input#middlename');
        $this->assertQuery('input#email');
        $this->assertQuery('select#hat');
        $attorneyHat = FixtureManager::getEntityManager()->getRepository('InterpretersOffice\Entity\Hat')
                        ->findByName('defense attorney')[0]->getId();
        $data = [
            'person' => [
                'lastname' => 'Somebody',
                'firstname' => 'John',
                'active' => 1,
                'hat' => $attorneyHat,            ],
            'csrf' => $this->getCsrfToken('/admin/people/add')
        ];
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($data)
        );
        $this->dispatch('/admin/people/add');
        $this->assertRedirect();
        $this->assertRedirectTo('/admin/people');
    }

    public function testFormValidation()
    {

        $attorneyHat = FixtureManager::getEntityManager()->getRepository('InterpretersOffice\Entity\Hat')
                        ->findByName('defense attorney')[0]->getId();
        $data = [
            'person' => [
                'lastname' => 'Somebody',
                'firstname' => 'John (Killer)',
                'active' => 1,
                'hat' => $attorneyHat,            ],
            'csrf' => $this->getCsrfToken('/admin/people/add')
        ];
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($data)
        );
        $this->dispatch('/admin/people/add');
        $this->assertNotRedirect();
        $this->assertQuery('div.validation-error');
        $this->assertQueryContentRegex('div.validation-error','/invalid characters/');

        
        $data['person']['lastname'] = '';
        $data['person']['firstname'] = 'John';
        $data['csrf'] = $this->getCsrfToken('/admin/people/add');
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($data)
        );
        $this->dispatch('/admin/people/add');
        $this->assertNotRedirect();
        $this->assertQuery('div.validation-error');
        $this->assertQueryContentRegex('div.validation-error','/last name.+required/');
        
        $data['person']['lastname'] = 'Somebody';
        $data['person']['firstname'] = '';
        $data['csrf'] = $this->getCsrfToken('/admin/people/add');
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($data)
        );
        $this->dispatch('/admin/people/add');
        $this->assertNotRedirect();
        $this->assertQuery('div.validation-error');
        $this->assertQueryContentRegex('div.validation-error','/first name.+required/');
        
        $data['person']['firstname'] = 'John';
        $data['csrf'] = $this->getCsrfToken('/admin/people/add');
        
        $data['person']['hat'] = '';
         $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($data)
        );
        $this->dispatch('/admin/people/add');
        $this->assertNotRedirect();
        $this->assertQuery('div.validation-error');
        $this->assertQueryContentRegex('div.validation-error','/hat.+required/');
        
        
        $data['person']['hat'] = $attorneyHat;
        $data['person']['active'] = null;
        $data['csrf'] = $this->getCsrfToken('/admin/people/add');
        
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($data)
        );
        $this->dispatch('/admin/people/add');
        
        $this->assertNotRedirect();
        $this->assertQuery('div.validation-error');
        $this->assertQueryContentRegex('div.validation-error','/active.+required/');
        
        $data['person']['active'] = 'some random string';
        $data['csrf'] = $this->getCsrfToken('/admin/people/add');
         $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($data)
        );
        $this->dispatch('/admin/people/add');
        $this->assertNotRedirect();

        $this->assertQueryCount('div.validation-error',1);
        $this->assertQueryContentRegex('div.validation-error','/invalid.+active/');
        
        
        $data['person']['active'] = 1;
        $data['csrf'] = $this->getCsrfToken('/admin/people/add');
        $data['person']['hat'] = 'arbitrary shit';
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($data)
        );
        $this->dispatch('/admin/people/add');
        $this->assertNotRedirect();
        $this->assertQueryCount('div.validation-error',1);
        $this->assertQueryContentRegex('div.validation-error','/invalid.+hat/');
       

    }

}
