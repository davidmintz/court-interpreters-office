<?php

/** 
 * module/Application/test/AuthenticationTest.php
 */
namespace ApplicationTest\Controller;


use ApplicationTest\AbstractControllerTest;

use Zend\Stdlib\Parameters;

use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;

use Application\Entity;
use Application\Service\Authentication;


use Zend\Authentication\AuthenticationService;

class AuthenticationTest extends AbstractControllerTest
{
    
    protected $auth;
    
    public function setUp()
    {
        $fixtureExecutor = FixtureManager::getFixtureExecutor();
        $fixtureExecutor->execute([
            new DataFixture\LanguageLoader(),
            new DataFixture\HatLoader(),
            new DataFixture\InterpreterLoader(),
            new DataFixture\LocationLoader(),
            new DataFixture\JudgeLoader(),
            new DataFixture\UserLoader(),
        ]);
        $adapter = new Authentication\Adapter([
            'object_manager' => FixtureManager::getEntityManager(),  //'Doctrine\ORM\EntityManager',
            'credential_property' => 'password',
            // 'credential_callable' => function (User $user, $passwordGiven) {
            //     return my_awesome_check_test($user->getPassword(), $passwordGiven);
            // },

            ]);
        $this->auth = new AuthenticationService(null, $adapter);
        parent::setUp();
    }
    
    public function testTest()
    {
        $adapter = $this->auth->getAdapter();//john_somebody@nysd.uscourts.gov
        $adapter->setIdentity('david@davidmintz.org')->setCredential('boink');
        
        // authentication will FAIL until we add the password_hash callback business
        
        $this->assertInstanceOf(AuthenticationService::class, $this->auth);
        $this->assertInstanceOf(\Application\Service\Authentication\Adapter::class, $adapter);
        //$result = $this->auth->authenticate();
        //echo "testing authentication... result is an instance of ";
        //echo get_class($result),'<br>';
        //echo $result->isValid() ? "auth OK" : "auth FAILED";
    }
    
}
