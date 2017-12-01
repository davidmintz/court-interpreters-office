<?php
/**
 * module/Admin/config/module.config.php
 * configuration for InterpretersOffice\Admin module.
 */

namespace InterpretersOffice\Admin;


use InterpretersOffice\Entity\Listener;

return [

    'router' => 
        include __DIR__.'/routes.php'
    ,
    'controllers' => [

        'invokables' => [
            Controller\AdminIndexController::class => Controller\AdminIndexController::class,
        ],
        'factories' => [
            Controller\LanguagesController::class => Controller\Factory\BasicEntityControllerFactory::class,
            Controller\LocationsController::class => Controller\Factory\BasicEntityControllerFactory::class,
            Controller\EventTypesController::class => Controller\Factory\BasicEntityControllerFactory::class,
            Controller\PeopleController::class => Controller\Factory\PeopleControllerFactory::class,
            Controller\JudgesController::class => Controller\Factory\PeopleControllerFactory::class,
            Controller\InterpretersController::class => Controller\Factory\PeopleControllerFactory::class,
            Controller\InterpretersWriteController::class => Controller\Factory\PeopleControllerFactory::class,
            Controller\UsersController::class => Controller\Factory\PeopleControllerFactory::class,
            Controller\EventsController::class => Controller\Factory\EventsControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_map' => [
            'interpreters-office/admin/admin-index/index' =>
                __DIR__.'/../view/interpreters-office/admin/index/index.phtml',
        ],
        'template_path_stack' => [
            'interpreters-office/admin' => __DIR__.'/../view',
        ],
    ],
    /** ACL configuration ====================================      **/
    // based on LearnZF2.
    
    'acl' => [
        'roles' => [
            // 'role name' => 'parent role'
            'anonymous' => null,
            'submitter' => null,
            'manager'   => null,
            'administrator' => 'manager',
            'staff' => null,
        ],
        /**
         * the keys in the following array refer to controllers, with the 
         * names normalized.
         * 
         */
        'resources' => [
             // 'resource name (controller)' => 'parent resource'
            'languages' => null,
            'event-types'=> 'languages',            
            'locations'=>'languages',
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
            // ??
            'vault' => null,
            'auth' => null,
            'administrator'=> null,
            // to be continued
        ],
        // how do we configure this to use Assertions?
        // I think we don't
        'allow' => [
            //'role' => [ 'resource (controller)' => [ priv, other-priv, ...  ]
            'submitter' => [
                'requests-index' => ['create','view','index'],
                'events'   => ['index','view','search'],
                'auth' => ['logout'],
            ],
            'manager' => [
                'admin-index' => null,
                'languages' => null,
                'events' => null,
                 // ??
                'vault' => null,
                'auth' => ['logout'],
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
                'requests-index' => null,//['add','edit','update','delete','cancel','index'],
            ],
            'anonymous' => [
                'auth' => 'logout'
            ],            
        ]
    ],
    'service_manager' => [
        'factories' => [
             Service\Acl::class  => Service\Factory\AclFactory::class,  
             Listener\InterpreterEntityListener::class => 
                Listener\Factory\InterpreterEntityListenerFactory::class,
             Listener\EventEntityListener::class => 
                Listener\Factory\EventEntityListenerFactory::class,
        ],
        'aliases' => [
            'acl' => Service\Acl::class,
            'interpreter-listener' => Listener\InterpreterEntityListener::class,
        ],
    ],
];
