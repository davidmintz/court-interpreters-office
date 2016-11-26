<?php

/**
 * module/Application/test/AbstractControllerTest.php
 */

namespace ApplicationTest;

use Zend\Stdlib\ArrayUtils;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;


/**
 * base class for unit tests
 */
class AbstractControllerTest extends AbstractHttpControllerTestCase
{
    
    public function setUp()
    {
        
        $configOverrides = [
            
            'module_listener_options' => [
                'config_glob_paths' => [
                    __DIR__.'/config/autoload/{{,*.}test,{,*.}local}.php'
                ],
            ],
            
        ];
        
        $this->setApplicationConfig(ArrayUtils::merge(
            include __DIR__ . '/../../../config/application.config.php',
            $configOverrides
        ));
       
       parent::setUp();
    }
    
}
