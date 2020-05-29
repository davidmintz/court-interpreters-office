<?php
namespace InterpretersOffice\Admin\Service;

use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use Laminas\Permissions\Acl\Role\RoleInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Laminas\Mvc\MvcEvent;

class ScheduleAccessAssertion implements AssertionInterface
{
    
    /** 
     * McvEvent
     * 
     * @var MvcEvent
     */
    private $event;

    public function __construct(MvcEvent $e)
    {
        $this->event = $e;    
    }


    /**
     * implements AssertionInterface
     *
     * @param  Acl    $acl
     * @param  RoleInterface $user
     * @param  ResourceInterface $controller
     * @param  string $privilege
     * @return boolean
     */
    public function assert(
        Acl $acl,
        RoleInterface $user = null,
        ResourceInterface $resource = null,
        $privilege = null
    ) {
        $event = $this->event;
        $container = $event->getApplication()->getServiceManager();
        $config = $container->get('config');
        $log = $container->get('log');
        $permissions = $config['permissions'] ?? [];
        if (! $permissions) {
            $log->info("local permissions not set for schedule access, access denied",
            ['channel'=>'security']);
            // return false;
        }
        /** @var Laminas\Http\PhpEnvironment\Request $request */
        $request = $event->getRequest();
        $domain =  $request->getServer()->get('HTTP_HOST') ?: 'office.localhost';       
        
        $allowed_domains = $permissions['schedule']['host_domains_allowed'] ?? [];
        if (in_array($domain, $allowed_domains)) {
            // $log->debug("domain $domain allows anonymous schedule access");
            return true;
        }
        $ip = $request->getServer()->get('REMOTE_ADDR');
        if (in_array($ip, $permissions['schedule']['anonymous_ips_allowed'])) {
            return true;
        }

        return false;
    }


}