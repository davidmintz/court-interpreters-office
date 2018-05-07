<?php
namespace ApplicationTest;

use Zend\Stdlib\ArrayUtils;
//use Zend\Stdlib\Parameters;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

//use Zend\Dom\Document;

class TestControllerTest extends AbstractHttpControllerTestCase
{

    public function setUp()
    {
        $configOverrides = [

            'module_listener_options' => [
                'config_glob_paths' => [
                    __DIR__.'/config/autoload/{{,*.}test,{,*.}local}.php',
                ],
            ],
        ];
        $config = ArrayUtils::merge(
            include __DIR__.'/../../../config/application.config.php',
            $configOverrides);

        $this->setApplicationConfig($config);
        parent::setUp();
    }

    public function testMainPage()
    {
        $this->dispatch('/');
        $this->assertResponseStatusCode(200);
        $this->assertQueryContentRegex('#database-info', '/database is test_office/');
    }

    public function testEntityManagerSanity()
    {
        $container = $this->getApplicationServiceLocator();
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('entity-manager');

        $this->assertTrue(is_object($em));
        $this->assertInstanceOf(\Doctrine\ORM\EntityManager::class, $em);

        $dbname = $em->getConnection()->getDatabase();
        $this->assertEquals('test_office',$dbname);

    }
}
