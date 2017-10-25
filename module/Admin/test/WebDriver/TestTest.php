<?php /** module/Admin/test/WebDriver/TestTest.php */

namespace ApplicationTest\WebDriver;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy as By;
//use Zend\Stdlib\ArrayUtils;
use PHPUnit\Framework\TestCase;
//use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * rough draft of a webdriver test. this whole thing is beset with 
 * horrible problems at the moment.
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
       
        // start Firefox with 5 second timeout
        parent::setUp();
        $host = 'http://localhost:4444/wd/hub'; // this is the default
        $capabilities = DesiredCapabilities::firefox();
        $driver = RemoteWebDriver::create($host, $capabilities, 5000);
        
        $this->driver = $driver;
    }
    
    public function tearDown()
    {
     parent::tearDown();
     $this->driver->quit();
    }
    
    public function testSomething()
    {
        $this->driver->get($this->base);
        
        $this->assertTrue(false !== stristr($this->driver->getTitle(),'Court Interpreters'));
        $element = $this->driver->findElement(By::cssSelector('h1'));
        $this->assertEquals('InterpretersOffice',$element->getText());
        $this->driver->get($this->base.'/admin');
        $this->assertTrue(false !== stristr($this->driver->getTitle(),'login'));
        $userField = $this->driver->findElement(By::cssSelector('#identity'));
        $userField->sendKeys('david');
        $passwordField = $this->driver->findElement(By::cssSelector('#password'));
        $passwordField->sendKeys('boink');
        $this->driver->findElement(By::cssSelector('button[type="submit"]'))->click();
        sleep(2);
    }

}
