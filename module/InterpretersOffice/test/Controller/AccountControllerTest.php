<?php

namespace ApplicationTest\Controller;

use ApplicationTest\AbstractControllerTest;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;

use Zend\Dom\Query;

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
            new DataFixture\HatLoader(),
            new DataFixture\JudgeLoader(),
        ]);
    }

    function testSubmitNewRegistration()
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
        $this->dispatch('/user/register','POST',$post);
        $json = $this->getResponse()->getBody();
        $response = json_decode($json);
        $this->assertTrue($response->status === "success");

    }

}
