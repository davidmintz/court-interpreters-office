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

class LanguagesControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        // The module configuration should still be applicable for tests.
        // You can override configuration here with test case specific values,
        // such as sample view templates, path stacks, module_listener_options,
        // etc.
        $configOverrides = [];

        $this->setApplicationConfig(ArrayUtils::merge(
            include __DIR__ . '/../../../../config/application.config.php',
            $configOverrides
        ));

        parent::setUp();
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
