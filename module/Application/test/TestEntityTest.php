<?php

/** @todo put this bit in a bootstrap file and add it to phpunit.xml */
require __DIR__. '/../../../vendor/autoload.php';

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\Stdlib\ArrayUtils;
    
class TestEntityTest extends AbstractHttpControllerTestCase //PHPUnit_Framework_TestCase
{
    
    public function setUp()
    {
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
        $this->assertTrue($container instanceof Interop\Container\ContainerInterface);
        
        $objectManager = $this->getEntityManager();
        $connection = $objectManager->getConnection();
        $driver = $connection->getDriver();
        $this->assertEquals('pdo_sqlite',$driver->getName());
       
    }
    
    public function _testSomethingElse() {
        
        $this->dispatch('/');
        $body = $this->getResponse()->getBody();
        echo $body;
    }
}
