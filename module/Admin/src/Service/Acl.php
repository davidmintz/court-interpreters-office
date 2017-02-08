<?php
/**
 * module/Admin/src/Service/Acl.php
 */
namespace InterpretersOffice\Admin\Service;

use Zend\Permissions\Acl\Acl as ZendAcl;



/**
 * ACL
 * 
 * 
 */
class Acl extends ZendAcl {
    
    
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
    public function __construct(Array $config)
    {
       $this->config = $config; 
       $this->setup();
    }
    
    /**
     * initialize the ACL
     */
    protected function setup()
            
    {       
        foreach($this->config['resources'] as $resource => $parent) {
            $this->addResource($resource, $parent);
        }
        foreach ($this->config['roles'] as $role => $parents) {
            $this->addRole($role,$parents);
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
        foreach($this->config['allow'] as $role => $rules ) {
           if (null === $rules) {
               $this->allow($role);
               continue;
           }
           foreach($rules as $resource => $privileges) {
               //printf ("we are setting allow on role %s, resource %s, privs %s<br>",$role,$resource, is_scalar($privileges)
               // ? $privileges : implode(",",$privileges));
               $this->allow($role,$resource,$privileges);               
           }            
        }        
    }
    /**
     * dumps some rules, for debugging
     * @todo move to unit test etc
     */
    public function testStuff()
    {
        //printf('<pre>%s</pre>',print_r($this->config,true));
        //return;
        echo '<pre>';
        echo "submitter update events? ";
        echo $this->isAllowed('submitter','events','update') ? "allowed" : "denied"; echo "<br>";
        
        echo "submitter boink events? ";
        echo $this->isAllowed('submitter','events','boink') ? "allowed" : "denied"; echo "<br>";
        
        echo "submitter edit judges? ";
        echo $this->isAllowed('submitter','judges','edit') ? "allowed" : "denied"; echo "<br>";
        
        echo "submitter create requests? ";
        echo $this->isAllowed('submitter','requests','create') ? "allowed" : "denied"; echo "<br>";
        
        echo "admin create requests? ";
        echo $this->isAllowed('administrator','requests','create') ? "allowed" : "denied"; echo "<br>";
        echo "manager update events? ";
        echo $this->isAllowed('manager','events','edit') ? "allowed" : "denied"; echo "<br>";
        
        echo "admin update events? ";
        echo $this->isAllowed('administrator','events','edit') ? "allowed" : "denied"; echo "<br>";
        
        echo "manager update event-types? ";
        echo $this->isAllowed('manager','event-types','edit') ? "allowed" : "denied"; echo "<br>";
        
        echo "manager update judges? ";
        echo $this->isAllowed('manager','event-types','edit') ? "allowed" : "denied"; echo "<br>";
        
        echo "manager edit users? ";
        echo $this->isAllowed('manager','users','edit') ? "allowed" : "denied"; echo "<br>";
        
        echo "manager create languages? ";
        echo $this->isAllowed('manager','languages','create') ? "allowed" : "denied"; echo "<br>";
         $this->allow('manager','languages');
        
        echo "admin create languages? ";
        echo $this->isAllowed('administrator','languages','create') ? "allowed" : "denied"; echo "<br>";
        
       echo "staff edit languages? ";
       echo $this->isAllowed('staff','languages','add') ? "allowed" : "denied"; echo "<br>";
        
        echo '</pre>';
        
        
    }
}
