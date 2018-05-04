<?php

/**
 * module/Application/test/AuthenticationTest.php.
 */

namespace ApplicationTest;

namespace ApplicationTest;

use PHPUnit\Framework\TestCase;

use ApplicationTest\Bootstrap;
use ApplicationTest\DataFixture;


use InterpretersOffice\Service\Authentication;
use Zend\Authentication\AuthenticationService;

class AuthenticationTest extends TestCase
{
    /**
     * @var Zend\Authentication\AuthenticationService
     */
    protected $auth;

    public function setUp()
    {
        $pdo = Bootstrap::getEntityManager()->getConnection()->getWrappedConnection();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        $executor = Bootstrap::getFixtureExecutor();

        $executor->execute([
            new DataFixture\Languages(),
            new DataFixture\Roles(),
            new DataFixture\Hats(),
            new DataFixture\Interpreters(),
            new DataFixture\Locations(),
            new DataFixture\Judges(),
            new DataFixture\Users(),        ]);
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        $adapter = new Authentication\Adapter(
            Bootstrap::getEntityManager()
        );
        $this->auth = new AuthenticationService(null, $adapter);
        parent::setUp();
    }

    public function testAuthenticateWithEmailAndWithUsername()
    {
        $adapter = $this->auth->getAdapter(); //john_somebody@nysd.uscourts.gov
        $adapter->setIdentity('david@davidmintz.org')->setCredential('boink');
        $this->assertInstanceOf(AuthenticationService::class, $this->auth);
        $this->assertInstanceOf(\InterpretersOffice\Service\Authentication\Adapter::class, $adapter);

        $result = $this->auth->authenticate();

        $this->assertInstanceOf(Authentication\Result::class, $result);
        $this->assertTrue($result->isValid());
        $this->auth->clearIdentity();
        $adapter->setIdentity('david')->setCredential('boink');
        $result = $this->auth->authenticate();
        $this->assertTrue($result->isValid());
        //echo "\n",$result->getCode(),"\n"; print_r($result->getMessages());
    }

    public function testAuthenticationFailsIfAccountIsNotActive()
    {
        $adapter = $this->auth->getAdapter();
        $adapter->setIdentity('david@davidmintz.org')->setCredential('boink');
        $em = Bootstrap::getEntityManager();
        $david = $em->getRepository('InterpretersOffice\Entity\User')
                ->findOneBy(['username' => 'david']);
        $david->setActive(false);
        $em->flush();
        $result = $this->auth->authenticate();
        $this->assertFalse($result->isValid());
        $this->assertEquals(
            Authentication\Result::FAILURE_USER_ACCOUNT_DISABLED,
            $result->getCode()
        );
    }

    public function testAuthenticationFailsIfPasswordIsWrong()
    {
        $adapter = $this->auth->getAdapter();
        $adapter->setIdentity('david@davidmintz.org')->setCredential('not correct');
        $result = $this->auth->authenticate();
        $this->assertFalse($result->isValid());
        $this->assertEquals(
            Authentication\Result::FAILURE_CREDENTIAL_INVALID,
            $result->getCode()
        );
    }
}
