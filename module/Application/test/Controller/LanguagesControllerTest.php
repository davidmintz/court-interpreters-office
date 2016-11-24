<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ApplicationTest\Controller;

use Application\Controller\LanguagesController;

use Zend\Stdlib\ArrayUtils;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;

use Application\Entity;

class LanguagesControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {

        ///*
        $configOverrides = [
            
            'module_listener_options' => [
                'config_glob_paths' => [
                    __DIR__.'/config/autoload/{{,*.}global,{,*.}local}.php'
                ],
            ],
            
        ];
        
        $this->setApplicationConfig(ArrayUtils::merge(
            include __DIR__ . '/../../../../config/application.config.php',
            $configOverrides
        ));
       // */
        
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

        // to be continued

        // sanity-check to be removed:
        $connection = $entityManager->getConnection();
        $driver = $connection->getDriver();
        $this->assertEquals('pdo_sqlite',$driver->getName());
        
        
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
