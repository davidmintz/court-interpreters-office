<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApplicationTest\WebDriver;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy as By;
//use Zend\Stdlib\ArrayUtils;
use PHPUnit\Framework\TestCase;
//use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Description of TestTest
 *
 * @author david
 */
class TestTest extends TestCase { //use Zend\Stdlib\ArrayUtils;
    
    /**
     * webdriver
     * 
     * @var RemoteWebDriver
     */
    protected $driver;
    
    protected $base = 'http://office.localhost';
    
    public function setUp()
    {
        /*
        $configOverrides = [

            'module_listener_options' => [
                'config_glob_paths' => [
                    __DIR__.'/config/autoload/{{,*.}test,{,*.}local}.php',
                ],
            ],
        ];
       
        $this->setApplicationConfig(ArrayUtils::merge(
            include __DIR__.'/../../../../config/application.config.php',
            $configOverrides
        ));

         */
        // start Firefox with 5 second timeout
        parent::setUp();
        $host = 'http://localhost:4444/wd/hub'; // this is the default
        $capabilities = DesiredCapabilities::firefox();
        $driver = RemoteWebDriver::create($host, $capabilities, 5000);
        
        $this->driver = $driver;
    }
    
    public function tearDown() {
     parent::tearDown();
     $this->driver->quit();
 }
    
    public function testSomething()
    {
        $this->driver->get($this->base);
        $title =  $this->driver->getTitle();
        $this->assertTrue(false !== stristr($title,'Court Interpreters'));
        //$element = $this->driver->findElement(By::cssSelector('a.navbar-brand'));
        //$this->assertTrue()
        $this->driver->get($this->base.'/admin');
        $this->sleep(2);
        
    }
    
}
