<?php

namespace ApplicationTest\Controller;

use ApplicationTest\AbstractControllerTest;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;

use Zend\Dom\Query;

use InterpretersOffice\Service\AccountManager;

class AccountControllerTest extends AbstractControllerTest
{

    protected $data = [
        'user' => [
            'person' => [
                'lastname' => 'Boinker',
                'firstname' => 'Wank',
                'hat' => 0,// placeholder
                'email' => 'wank_boinker@nysd.uscourts.gov',
            ],
            'judges' => [],// also a placeholder
            'password' => 'fuck you',
            'password-confirm' => 'fuck you',
            'username' => 'boinker',
        ],

    ];
    public function setUp()
    {
        parent::setUp();
        $fixtureExecutor = FixtureManager::getFixtureExecutor();
        $fixtureExecutor->execute([
            new DataFixture\LocationLoader(),
            new DataFixture\MinimalUserLoader(),
            new DataFixture\JudgeLoader(),
        ]);
    }

    function testNewUserRegistration()
    {

        $this->dispatch('/user/register');
        $this->assertResponseStatusCode(200);
        $this->assertQuery("input#csrf");
        $dom = new Query($this->getResponse()->getBody());
        $csrf_element = $dom->execute("input#csrf")->current();
        $this->assertTrue(is_object($csrf_element));
        $token = $csrf_element->getAttribute('value');
        $this->assertTrue(is_string($token));

        // figure out the id of our hat
        $hat_id = null;
        $judge_staff = false;
        $hats = $dom->execute('select#hat > option');
        $this->assertGreaterThan(3, count($hats));
        foreach ($hats as $option) {
            if ('Law Clerk' == $option->textContent) {
                $hat_id = $option->getAttribute('value');
                $judge_staff = $option->getAttribute('data-is_judges_staff');
                break;
            }
        }
        $this->assertTrue((boolean)$judge_staff);

        // choose a judge
        $judge_id = null;
        $judges = $dom->execute('select#judge-select > option');
        foreach($judges as $judge) {
            if (strstr($judge->nodeValue,'Dinklesnort')) {
                $judge_id = $judge->getAttribute('value');
                break;
            }
        }
        $this->assertTrue(is_string($judge_id));
        $this->assertGreaterThan(0,(int)$judge_id);

        $this->reset(true);
        $this->getRequest()->getHeaders()
            ->addHeaderLine('X-Requested-With','XMLHttpRequest');
        $post = $this->data;
        $post['csrf'] = $token;
        $post['user']['person']['hat'] = $hat_id;
        $post['user']['judges'][] = $judge_id;
        $post['user']['id']='';
        /** @var $sharedEvents Zend\EventManager\SharedEventManagerInterface */
        //$sharedEvents = $this->getApplicationServiceLocator()->get('SharedEventManager');
        //$this->prophet = $accountManager = $this->prophesize(AccountManager::class);
        // $sharedEvents->attach('InterpretersOffice\Controller\AccountController',
        //     AccountManager::EVENT_REGISTRATION_SUBMITTED,
        //     [$accountManager->reveal(),'register']
        // );
        $this->dispatch('/user/register','POST',$post);
        $response = $this->getResponse()->getBody();
        echo $response;
        $obj = json_decode($response);
        $this->assertTrue($obj->status === "success");
        // $accountManager->register(
        //     \Prophecy\Argument::type(\InterpretersOffice\Entity\User::class),
        //     \Prophecy\Argument::type(\Zend\Http\PhpEnvironment\Request::class))
        //     ->shouldBeCalled();

        /** @var $accountManager \InterpretersOffice\Service\AccountManager */
        $accountManager = $this->getApplication()->getServiceManager()
            ->get('InterpretersOffice\Service\AccountManager');
        $result = $accountManager->verify(md5($post['user']['person']['email']),
            $accountManager->getRandomString(),AccountManager::CONFIRM_EMAIL);
        $this->assertTrue(is_array($result));
        $this->assertNull($result['error'],"failed assertion \$result[error] is NULL");
        $this->assertTrue(is_array($result['data']));

        $this->reset();
        $url = $accountManager->getUrl();
        $this->dispatch($url,'GET');
        $this->assertQuery('div.alert-success');
        $this->assertQueryContentRegex('div.alert-success', '/account.+activated/');

        // log in as the new user
        $this->reset();
        $token = $this->getCsrfToken('/login', 'login_csrf');

        // sanity-check that we are not yet authenticated
        $auth = $this->getApplicationServiceLocator()->get('auth');
        $this->assertFalse($auth->hasIdentity());

        $this->dispatch('/login','POST',[
            'identity' => $this->data['user']['person']['email'],
            'password' => 'fuck you',
            'login_csrf' => $token,
        ]);
        // we should be redirected
        $this->assertRedirect();
        // and we should be authenticated
        $this->assertTrue($auth->hasIdentity());

    }

    public function __testPasswordReset()
    {
        $this->dispatch('/user/request-password');
    }
}
