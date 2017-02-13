<?php
/**
 * module/Application/test/AclTest.php.
 */

namespace ApplicationTest;

use ApplicationTest\AbstractControllerTest;
use InterpretersOffice\Admin\Service\Acl;




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
        $this->acl = $this->getApplicationServiceLocator()->get('acl');
    }
    
    public function testAclIsAvailableFromServiceManager()
    {
        $this->assertInstanceOf(Acl::class,$this->acl );
    }
    
    public function testAcl()
    {
        
        
        $this->assertFalse($this->acl->isAllowed('submitter','events','update'),"submitter should NOT be allowed to edit events");
        $this->assertFalse($this->acl->isAllowed('submitter','events','boink'),"submitter should NOT be allowed undefined privilege");
        $this->assertFalse($this->acl->isAllowed('submitter','judges','edit'),"submitters should NOT be allowed to edit judges" );
        $this->assertTrue($this->acl->isAllowed('submitter','requests','create') ,"submitted SHOULD be allowed to create a request");
        $this->assertTrue($this->acl->isAllowed('manager','events','edit'),"manager SHOULD be allowed to edit events");
        $this->assertTrue($this->acl->isAllowed('administrator','events','edit'),"admin SHOULD be allowed to edit events");
        $this->assertTrue($this->acl->isAllowed('manager','event-types','edit'));
        // too tedious! try something else....
        $allow = [
            ['manager','event-types','edit'],
            ['manager','users','edit'],
            ['manager','users','add'],
            ['manager','languages','edit'],
            ['administrator','languages','edit'],
        ];
        foreach ($allow as $rule) {
            list($role, $resource, $privilege) = $rule;
            $this->assertTrue($this->acl->isAllowed($role, $resource, $privilege),"$role SHOULD be allowed to $privilege $resource");
        }
        $deny = [
            
            ['staff','event-types','edit'],
            ['staff','languages','add'],
            ['staff','users','add'],
            ['administrator','requests','edit'],
            ['staff','requests','edit'],
            ['staff','requests','edit'],
            
            
        ];
        foreach ($deny as $rule) {
            list($role, $resource, $privilege) = $rule;
            $this->assertFalse($this->acl->isAllowed($role, $resource, $privilege),"$role SHOULD NOT be allowed to $privilege $resource");
        }
        
    }
    
    
}