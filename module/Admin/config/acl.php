<?php
/** module/Admin/config/acl.php
==========  ACL configuration ====================================      
// based on LearnZF2.
*/
    

return [
    'roles' => [
        // 'role name' => 'parent role'
        'anonymous' => null,
        'submitter' => null,
        'manager' => null,
        'administrator' => 'manager',
        'staff' => null,
    ],
    /**
     * most of the keys in the following array refer to controllers, with the 
     * names normalized.
     * 
     */
    'resources' => [
        // 'resource name (controller)' => 'parent resource'
        'languages' => null,
        'event-types' => 'languages',
        'locations' => 'languages',
        'events' => null,
        'users' => 'events',
        'people' => 'users',
        'judges' => 'events',
        'interpreters' => 'events',
        'interpreters-write' => 'events',
        // the topmost controller
        'index' => null,
        'requests-index' => null,
        'admin-index' => null,        
        'vault' => null,
        'auth' => null,
        // these refer to user resource ids. the User entity implements 
        // Zend\Permissions\Acl\Resource\ResourceInterface
        'administrator' => null,
        'manager' => null,
        'submitter' => null,
        'staff'=> null,
    ],
    // how do we configure this to use Assertions?$
    // I think we don't
    'allow' => [
        //'role' => [ 'resource (controller)' => [ priv, other-priv, ...  ]
        'submitter' => [
            'requests-index' => ['create', 'view', 'index'],
            'events' => ['index', 'view', 'search'],
            'auth' => ['logout'],
        ],
        'manager' => [
            'admin-index' => null,
            'languages' => null,
            'events' => null,
            // ??
            'vault' => null,
            'auth' => ['logout'],
            'submitter' => null,
        ],
        'staff' => [
            'admin-index' => ['index'],
            'auth' => ['logout'],
        ],
        'administrator' => null,
        'anonymous' => [
            'auth' => 'login',
        ]
    ],
    'deny' => [
        'administrator' => [
            'requests-index' => null, //['add','edit','update','delete','cancel','index'],
        ],
        'anonymous' => [
            'auth' => 'logout'
        ],
    ]
];
