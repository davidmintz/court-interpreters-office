<?php
/**
 * module/Application/test/AclTest.php.
 */

namespace ApplicationTest;

use ApplicationTest\AbstractControllerTest;
use InterpretersOffice\Admin\Service\Acl;

use InterpretersOffice\Admin\Controller as Admin;
use InterpretersOffice\Requests\Controller as Requests;

class AuthenticationTest extends AbstractControllerTest
{

    /**
     *
     * @var Acl
     */
    protected $acl;

    public function setUp()
    {
        parent::setUp();
        $config = $this->getApplicationServiceLocator()->get('config')['acl'];
        // set it up outside the MVC context because the event listeners assume
        // an authenticated user, which we do not have
        $this->acl = new Acl($config);
        $this->acl->setEventManager(new \Laminas\EventManager\EventManager());
    }

    public function testAclIsAvailableFromServiceManager()
    {
        $this->assertInstanceOf(Acl::class, $this->getApplicationServiceLocator()->get('acl'));
    }

    public function testAclRules()
    {

        $this->assertFalse($this->acl->isAllowed('submitter', Admin\EventsController::class, 'update'), "submitter should NOT be allowed to edit events");
        $this->assertFalse($this->acl->isAllowed('submitter', Admin\EventsController::class, 'boink'), "submitter should NOT be allowed undefined privilege");
        $this->assertFalse($this->acl->isAllowed('submitter', Admin\JudgesController::class, 'edit'), "submitters should NOT be allowed to edit judges");
        $this->assertTrue($this->acl->isAllowed('submitter', Requests\IndexController::class, 'create'), "submitted SHOULD be allowed to create a request");
        $this->assertTrue($this->acl->isAllowed('manager', Admin\EventsController::class, 'edit'), "manager SHOULD be allowed to edit events");
        $this->assertTrue($this->acl->isAllowed('administrator', Admin\EventsController::class, 'edit'), "admin SHOULD be allowed to edit events");
        $this->assertTrue($this->acl->isAllowed('manager', Admin\EventTypesController::class, 'edit'));
        // too tedious! try something else....
        $allow = [
            ['manager',Admin\EventTypesController::class,'edit'],
            ['manager',  Admin\UsersController::class,'edit'],
            ['manager',Admin\UsersController::class,'add'],
            ['manager',Admin\LanguagesController::class,'edit'],
            ['administrator',Admin\LanguagesController::class,'edit'],
        ];
        foreach ($allow as $rule) {
            list($role, $resource, $privilege) = $rule;
            $this->assertTrue($this->acl->isAllowed($role, $resource, $privilege), "$role SHOULD be allowed to $privilege $resource");
        }
        $deny = [

            ['staff',Admin\EventTypesController::class,'edit'],
            ['staff',Admin\LanguagesController::class,'add'],
            ['staff',Admin\UsersController::class,'add'],
            ['administrator',Requests\IndexController::class,'edit'],
            ['staff',Requests\IndexController::class,'edit'],
            ['staff',Requests\IndexController::class,'edit'],


        ];
        foreach ($deny as $rule) {
            list($role, $resource, $privilege) = $rule;
            $this->assertFalse($this->acl->isAllowed($role, $resource, $privilege), "$role SHOULD NOT be allowed to $privilege $resource");
        }
    }
}
