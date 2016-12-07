<?php

/**
 * module/InterpretersOffice/test/AbstractControllerTest.php
 */

namespace ApplicationTest;

use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\Parameters;
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
    /**
     * logs in a user through the AuthController
     * 
     * @param string $identity
     * @param string $password
     * @return AbstractControllerTest
     */
    public function login($identity,$password)
    {
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters(
                [
                    'identity' => 'susie',
                    'password' => 'boink'
                ]
            )
        );
        $this->dispatch('/login');
        $this->reset(true);
        
    }
}
