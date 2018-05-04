<?php

/** module/Application/test/Controller/AuthControllerTest.php */

namespace ApplicationTest\Controller;

use ApplicationTest\AbstractControllerTest;
use ApplicationTest\Bootstrap;
use ApplicationTest\DataFixture;
use ApplicationTest\DataFixture\Interpreters;
use InterpretersOffice\Entity\User;

class AuthControllerTest extends AbstractControllerTest
{
    public function setUp()
    {
        parent::setUp();
        Bootstrap::load([
            new DataFixture\Roles(),
            new DataFixture\Hats(),
            new DataFixture\LocationTypes(),
            new DataFixture\Locations(),
            new DataFixture\Judges(),
            new DataFixture\Languages(),
            new DataFixture\Interpreters(),
            new DataFixture\Users(),
        ]);
    }
    /**
     * asserts that susie_somebody@nysd.uscourts.gov can log in
     * with either username or email address.
     */
    public function testLoginAdminUser()
    {
        $token = $this->getCsrfToken('/login', 'login_csrf');
        $auth = $this->getApplicationServiceLocator()->get('auth');
        $em = $this->getApplicationServiceLocator()->get('entity-manager');
        $user = $em->getRepository(User::class)->findOneBy(['username'=>'susie']);
        $this->assertInstanceOf(User::class, $user);
        $hash = $user->getPassword();
        $valid = password_verify('boink',$hash);
        $params =
        [
            'identity' => 'susie',
            'password' => 'boink',
            'login_csrf' => $token,
        ];
        $this->dispatch('/login', 'POST', $params);
        //$this->dumpResponse();
        $this->assertResponseStatusCode(302);
        $this->assertRedirect();
        $this->assertRedirectTo('/admin');
        $this->assertTrue($auth->hasIdentity(), 'failed asserting that $auth->hasIdentity()');

        $auth->clearIdentity();
        // just checking :-)
        $this->assertFalse($auth->hasIdentity());

        // now try it using email instead of username
        $params['identity'] = 'susie_somebody@nysd.uscourts.gov';
        $params['csrf'] = $this->getCsrfToken('/login', 'login_csrf');
        $this->dispatch('/login', 'POST', $params);
        $this->assertTrue($auth->hasIdentity());
        $this->assertRedirect();
        $this->assertRedirectTo('/admin');
    }
    /**
     * tests that an admin user who is redirected from an admin page to
     * the login will be redirected back after successful login.
     */
    public function testRedirectionFollowingAuthentication()
    {
        $this->dispatch('/admin/languages/add');
        $this->assertRedirect();
        $this->assertRedirectTo('/login');
        //echo "\n".$_SESSION['Authentication']->redirect_url . " is our shit here in ".__FUNCTION__."...\n";
        $this->reset(true);
        $params =
        [
            'identity' => 'susie',
            'password' => 'boink',
            'login_csrf' => $this->getCsrfToken('/login', 'login_csrf'),
        ];
        $this->dispatch('/login', 'POST', $params);
        $this->assertRedirect();
        $this->assertRedirectTo('/admin/languages/add');

        $this->dispatch('/logout');

        // demote susie to see what happens next time she tries to access an admin page
        $em = Bootstrap::getEntityManager();
        $user = $em->getRepository('InterpretersOffice\Entity\User')->findOneBy(['username' => 'susie']);
        $role = $em->getRepository('InterpretersOffice\Entity\Role')->findOneBy(['name' => 'submitter']);

        $user->setRole($role);
        $em->flush();

        $this->dispatch('/admin/languages/add');
        $this->assertRedirect();
        $this->reset(true);
        $params =
        [
            'identity' => 'susie',
            'password' => 'boink',
            'login_csrf' => $this->getCsrfToken('/login', 'login_csrf'),
        ];
        $this->dispatch('/login', 'POST', $params);
        $this->assertRedirect();
        $this->assertNotRedirectTo('/admin/languages/add');
    }

    /**
     * asserts that a non-administrative user cannot go to /admin.
     */
    public function testNonAdministrativeUserCannotAccessAdmin()
    {
        // demote user Susie
        $entityManager = Bootstrap::getEntityManager();
        $susie = $entityManager->getRepository('InterpretersOffice\Entity\User')
                ->findByUsername('susie')[0];
        $susie->setRole(
            $entityManager->getRepository('InterpretersOffice\Entity\Role')
                ->findByName('submitter')[0]
        );
        $entityManager->flush();
        // sanity-check it first
        $this->assertEquals('submitter', (string) $susie->getRole());

        $token = $this->getCsrfToken('/login', 'login_csrf');
        $params =
        [
            'identity' => 'susie',
            'password' => 'boink',
            'login_csrf' => $token,
        ];
        $this->dispatch('/login', 'POST', $params);
        $this->assertRedirect();
        $this->assertNotRedirectTo('/admin');

        $this->dispatch('/admin');
        $this->assertRedirect();
        $this->assertResponseStatusCode(303);
    }
    /**
     * asserts that the wrong identity or password fails to authenticate.
     */
    public function testBadLoginNameOrPasswordFailsToAuthenticate()
    {
        // previous assertions established that the password is 'boink'
        // so anything else should fail
        $this->dispatch(
            '/login',
            'POST',
            ['identity' => 'susie',
                 'password' => 'notCorrect',
                 'login_csrf' => $this->getCsrfToken('/login', 'login_csrf'),
               ]
        );
        $this->assertNotRedirect();
        $auth = $this->getApplicationServiceLocator()->get('auth');
        $this->assertFalse($auth->hasIdentity());
        $this->assertQuery('div.alert-warning');
        $this->assertQueryContentRegex('div.alert-warning', '/authentication failed/i');
        $this->dispatch(
            '/login',
            'POST',
            ['identity' => 'nobody',
                     'password' => 'notCorrect',
                     'login_csrf' => $this->getCsrfToken('/login', 'login_csrf'),
                 ]
        );
        $this->assertNotRedirect();
        $this->assertFalse($auth->hasIdentity());
        $this->assertQuery('div.alert-warning');
        $this->assertQueryContentRegex('div.alert-warning', '/authentication failed/i');
    }
}
