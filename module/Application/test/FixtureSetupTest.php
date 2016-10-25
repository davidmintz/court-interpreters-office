<?php
namespace ApplicationTest;


use Zend\Stdlib\ArrayUtils;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
//use PHPUnit_Framework_TestCase;

/** @todo put this bit in a bootstrap file and add it to phpunit.xml ? */

//require __DIR__. '/../../../vendor/autoload.php';
    
class FixtureSetupTest extends  
   // PHPUnit_Framework_TestCase
   AbstractHttpControllerTestCase
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
            include __DIR__ . '/../../../config/application.config.php',
            $configOverrides
        ));
       // */
        parent::setUp();
        
        
    }
    /**
     * @return Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->getApplicationServiceLocator()->get('entity-manager');
    }
    public function testSomething()
    {       
        
        $container = $this->getApplicationServiceLocator();
        $this->assertTrue($container instanceof \Interop\Container\ContainerInterface);
        
        $objectManager = $this->getEntityManager();
        $connection = $objectManager->getConnection();
        $driver = $connection->getDriver();
        $this->assertEquals('pdo_sqlite',$driver->getName());
        $this->dispatch('/');
        $this->assertResponseStatusCode(200);
       
    }
    
    public function testDataFixtureSanity() {
        
        $this->assertTrue(class_exists('ApplicationTest\FixtureManager'));
        $shit = FixtureManager::getFixtureExecutor();
        $this->assertTrue(is_object($shit));
        $fixtureExecutor = FixtureManager::getFixtureExecutor();
        //FixtureManager::start();
        $fixtureExecutor->execute([
            new DataFixture\LanguageLoader(),
            new DataFixture\HatLoader(),
            new DataFixture\EventTypeLoader(),
            new DataFixture\LocationLoader(),
            new DataFixture\DefendantNameLoader(),
            new DataFixture\JudgeLoader(),
            new DataFixture\InterpreterLoader(),
            new DataFixture\CancellationReasonLoader(),
            new DataFixture\UserLoader(),
            new DataFixture\EventLoader(),
         ]);
        $entityManager = FixtureManager::getEntityManager();
        $this->assertTrue(is_object($entityManager));
        //echo get_class($entityManager);
        $languages = $entityManager->getRepository('Application\Entity\Language')->findAll();
        $this->assertTrue(is_array($languages));
        /** @var Doctrine\DBAL\Connection $connection */
        $connection = $entityManager->getConnection();
        $count = (int) $connection->fetchColumn("select count(*) from languages");
        $this->assertEquals($count,count($languages));

    }
}


