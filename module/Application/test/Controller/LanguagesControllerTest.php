<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ApplicationTest\Controller;

use Application\Controller\LanguagesController;
use ApplicationTest\AbstractControllerTest;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\Parameters;

use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;

use Application\Entity;

class LanguagesControllerTest extends AbstractControllerTest
{
    public function setUp()
    {

         
        $fixtureExecutor = FixtureManager::getFixtureExecutor();
        $fixtureExecutor->execute([new DataFixture\LanguageLoader()]);
        parent::setUp();
    }
        //$fixtureExecutor = FixtureManager::getFixtureExecutor();
        //$fixtureExecutor->execute([new DataFixture\LanguageLoader()]);
        //parent::setUp(); 
    
     public function testAddLanguage()
    {
        $entityManager = FixtureManager::getEntityManager();//$this->getApplicationServiceLocator()->get('entity-manager');
        $repository = $entityManager->getRepository('Application\Entity\Language');
        $languages = $repository->findAll(); 
        $this->assertTrue(is_array($languages));
        $count_before = count($languages);
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters(
                [
                    'name' => 'Vulcan',
                    'comments' => 'rarely used'
                ]
            )
        );
        $this->dispatch('/admin/languages/add');

        $this->assertRedirect();
        $this->assertRedirectTo('/admin/languages');
        $count_after = count($repository->findAll());
        $this->assertEquals(1, $count_after - $count_before);
        
        
    }
    
    public function testLanguagesIndexActionCanBeAccessed()
    {
        $this->dispatch('/admin/languages', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('application');
        // as specified in router's controller name alias...
        $this->assertControllerName(LanguagesController::class);

        $this->assertControllerClass('LanguagesController');
        $this->assertMatchedRouteName('languages');
    }
    public function testLanguagesEditActionCanBeAccessed()
    {
        $this->dispatch('/admin/languages/edit/1', 'GET',[]);
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('application');
        // as specified in router's controller name alias...
        $this->assertControllerName(LanguagesController::class);
        $this->assertMatchedRouteName('languages/edit');
    }

    
    
}
