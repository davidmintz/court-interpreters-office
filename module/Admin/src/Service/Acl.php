<?php
/**
 * module/Admin/src/Service/Acl.php
 */
namespace InterpretersOffice\Admin\Service;

use Laminas\Permissions\Acl\Acl as LaminasAcl;

use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\EventManager\EventManagerAwareInterface;

/**
 * ACL
 *
 * doesn't seem to be necessary to implement EventManagerAwareInterface
 * explicitly if we call setEventManager on this ourselves
 *
 */
class Acl extends LaminasAcl implements EventManagerAwareInterface
{

    use EventManagerAwareTrait;

    /**
     * configuration
     *
     * @var Array
     */
    protected $config;


    /**
     * constructor
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->setup();
    }

    /**
     * initializes the ACL
     */
    protected function setup()
    {
        foreach ($this->config['resources'] as $resource => $parent) {
            $this->addResource($resource, $parent);
        }
        foreach ($this->config['roles'] as $role => $parents) {
            $this->addRole($role, $parents);
        }
        /*
        'allow' => [
            //'role' => [ 'resource' => [ priv, other-priv, ...  ]
            'submitter' => [
                'requests' => ['create','view','index'],
                'events'   => ['index','view','search'],
            ],
            'manager' => [
                'languages' => null,
                'events' => null,
            ],
            'administrator' => null,
        ],
         */
        foreach ($this->config['allow'] as $role => $rules) {
            if (null === $rules) {
                $this->allow($role);
                continue;
            }
            foreach ($rules as $resource => $privileges) {
                //printf ("we are setting allow on role %s, resource %s, privs %s<br>",$role,$resource, is_scalar($privileges)
                // ? $privileges : implode(",",$privileges));
                $this->allow($role, $resource, $privileges);
            }
        }
        foreach ($this->config['deny'] as $role => $rules) {
            if (null === $rules) {
                $this->deny($role);
                continue;
            }
            foreach ($rules as $resource => $privileges) {
                 // printf ("we are setting allow on role %s, resource %s,
                 // privs %s<br>",$role,$resource, is_scalar($privileges)
                 // ? $privileges : implode(",",$privileges));
                 $this->deny($role, $resource, $privileges);
            }
        }
    }

    /**
     * Returns true if the Role has access to the Resource
     *
     * Overrides the parent, the sole difference being that we trigger an
     * 'access-denied' event if applicable.
     *
     * @param \Laminas\Permissions\Acl\Role\RoleInterface|string            $role
     * @param \Laminas\Permissions\Acl\Resource\ResourceInterface|string    $resource
     * @param string $privilege
     * @return bool true if authorized
     *
     */
    public function isAllowed($role = null, $resource = null, $privilege = null)
    {
        $allowed = parent::isAllowed($role, $resource, $privilege);
        if (! $allowed) {
            $this->events->trigger(
                'access-denied',
                $this,
                \compact('role', 'resource', 'privilege')
            );
        }
        return $allowed;
    }
}
