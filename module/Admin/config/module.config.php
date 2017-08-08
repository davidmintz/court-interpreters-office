<?php

/**
 * configuration for InterpretersOffice\Admin module.
 */

namespace InterpretersOffice\Admin;

use Zend\Router\Http\Segment;
use Zend\Router\Http\Literal;

use InterpretersOffice\Entity\Listener;

return [

    'router' => [
        // this is too big. needs to be broken up into separate files
        'routes' => [
            'admin' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/admin', //[/]
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\AdminIndexController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'events' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/admin/schedule',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\EventsController::class,
                        'action' => 'index',
                    ],
                ],
                'child_routes' => [
                    'add' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/add',
                            'defaults' => [
                                'action' => 'add',
                            ],
                        ],
                    ],
                    'edit' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/:action/:id',
                            'defaults' => [
                                'action' => 'edit',
                            ],
                            'constraints' => [
                                'action' => 'edit|delete|repeat',
                                'id' => '[1-9]\d*',
                            ],
                        ],
                    ],
                ],
            ],
            'languages' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/admin/languages',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\LanguagesController::class,
                        'action' => 'index',
                    ],
                ],
                'child_routes' => [
                    'add' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/add',
                            'defaults' => [
                                'action' => 'add',
                            ],
                        ],
                    ],
                    'edit' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/:action/:id',
                            'defaults' => [
                                'action' => 'edit',
                            ],
                            'constraints' => [
                                'action' => 'edit|delete',
                                'id' => '[1-9]\d*',
                            ],
                        ],
                    ],
                ],
            ],
            'locations' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [

                    'route' => '/admin/locations',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\LocationsController::class,
                        'action' => 'index',
                    ],
                ],
                'child_routes' => [
                    'type' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/type/:id',
                            'defaults' => [
                                'action' => 'index',
                            ],
                            'constraints' => [
                                'id' => '[1-9]\d*',
                            ],
                        ],
                    ],

                    /* @todo this will have to be moved or copied to a
                     * non-admin controller but this is convenient for now
                     */
                    'courtrooms' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/courtrooms/:parent_id',
                            'defaults' => [
                                'action' => 'courtrooms',
                            ],
                            'constraints' => [
                                'parent_id' => '[1-9]\d*',
                            ],
                        ],
                    ],
                    'add' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/add[/type/:type_id]',
                            'defaults' => [
                                'action' => 'add',
                            ],
                            'constraints' => [
                                'type_id' => '[1-9]\d*',
                            ],
                        ],
                    ],
                    'edit' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/:action/:id',
                            'defaults' => [
                                'action' => 'edit',

                            ],
                            'constraints' => [
                                'action' => 'edit|delete',
                                'id' => '[1-9]\d*',
                            ],
                        ],
                    ],

                ],
            ],
            'event-types' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/admin/event-types',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\EventTypesController::class,
                        'action' => 'index',
                    ],
                ],
                'child_routes' => [
                     'add' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/add',
                            'defaults' => [
                                'action' => 'add',
                            ],
                        ],
                    ],
                    'edit' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/:action/:id',
                            'defaults' => [
                                'action' => 'edit',

                            ],
                            'constraints' => [
                                'action' => 'edit|delete',
                                'id' => '[1-9]\d*',
                            ],
                        ],
                       
                    ],
                ],
            ],
            'people' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/admin/people',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\PeopleController::class,
                        'action' => 'index',
                    ],
                ],
                'child_routes' => [
                    'add' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/add',
                            'defaults' => [
                                'action' => 'add',
                            ],
                        ],
                    ],
                    'edit' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/:action/:id',
                            'defaults' => [
                                'action' => 'edit',

                            ],
                            'constraints' => [
                                'action' => 'edit|delete',
                                'id' => '[1-9]\d*',
                            ],
                        ],
                    ],
                ],
            ],
            'judges' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/admin/judges',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\JudgesController::class,
                        'action' => 'index',
                    ],
                ],
                'child_routes' => [
                    'add' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/add',
                            'defaults' => [
                                'action' => 'add',
                            ],
                        ],
                    ],
                    'edit' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/:action/:id',
                            'defaults' => [
                                'action' => 'edit',

                            ],
                            'constraints' => [
                                'action' => 'edit|delete',
                                'id' => '[1-9]\d*',
                            ],
                        ],
                    ],
                ],
            ],

            'interpreters' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/admin/interpreters',//[/list] was an experiment
                    
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\InterpretersController::class,
                        'action' => 'index',
                        // defaults for interpreter roster search terms
                        //'active' => 1, // by default, active only
                        //'security_clearance_expiration'=> 1, // by default, valid security clearance status
                        //'language_id' => 0,
                        // 'name' => '',

                    ],
                    
                ],
                'child_routes' => [
                    'add' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/add',
                            'defaults' => [
                                'action' => 'add',
                            ],
                        ],
                    ],
                    'find_by_id' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/:id',
                            'defaults' => [
                                'action' => 'index',
                            ],
                            'constraints' => [                                
                                'id' => '[1-9]\d*',
                            ],
                        ],
                    ],
                    'edit' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/:action/:id',
                            'defaults' => [
                                'action' => 'edit',
                            ],
                            'constraints' => [
                                'action' => 'edit|delete',
                                'id' => '[1-9]\d*',
                            ],
                        ],
                    ],

                    'find_by_name' => [ 
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/name/:lastname[/:firstname]',
                            'defaults' => [
                                'action' => 'index',
                                // for name-search text input
                                //'name' => '',
                                //'firstname'   => '',
                            ],                           
                        ],
                    ],
                    ///*
                    'find_by_language' => [ 
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/language/:language_id[/active/:active[/security/:security_clearance_expiration]]',
                            'constraints' => [                                
                                'language_id' => '[0-9]\d*',
                                'active' => '-?1|0',
                                // any value, as long as it's -2, -1, 0 or 1
                                'security_clearance_expiration' => '-[12]|[01]',
                            ],
                        ],
                    ], 
                    'validate-partial'  => [

                        'type' => Segment::class,
                        'options' => [
                            'route' => '/validate-partial',
                            'defaults' => [
                                'action' => 'validate-partial',
                            ],
                        ],
                    ],           
                ],
            ],

            'users' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/admin/users',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\UsersController::class,
                        'action' => 'index',
                    ],
                ],
                'child_routes' => [
                    'add' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/add[/person/:id]',
                            'defaults' => [
                                'action' => 'add',
                            ],
                            'constraints' => [
                                'id' => '[1-9]\d*',
                            ]
                        ],
                    ],
                    'edit' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/:action/:id',
                            'defaults' => [
                                'action' => 'edit',

                            ],
                            'constraints' => [
                                'action' => 'edit|delete',
                                'id' => '[1-9]\d*',
                            ],
                        ],
                    ],
                ],
            ],
            'vault-test' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/vault/test', //[/]
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\VaultController::class,
                        'action' => 'test',
                    ],
                ],
            ],
            'vault-authenticate' => [
             'type' => Literal::class,
                'options' => [
                    'route' => '/vault/authenticate-app', //[/]
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\VaultController::class,
                        'action' => 'authenticate-app',
                    ],
                ],
            ],
        ],
    ],
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
            'requests-index' => null,
            'admin-index' => null,
            // ??
            'vault' => null,
            'auth' => null,
            // to be continued
        ],
        // how do we configure this to use Assertions?
        // I think we don't
        'allow' => [
            //'role' => [ 'resource (controller)' => [ priv, other-priv, ...  ]
            'submitter' => [
                'requests-index' => ['create','view','index'],
                'events'   => ['index','view','search'],
            ],
            'manager' => [
                'admin-index' => null,
                'languages' => null,
                'events' => null,
                 // ??
                'vault' => null,
            ],
            'staff' => [
                'admin-index' => ['index'],
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
            ]
        ]
    ],
    'service_manager' => [
        'factories' => [
             Service\Acl::class  => Service\Factory\AclFactory::class,  
             Listener\InterpreterEntityListener::class => \InterpretersOffice\Entity\Listener\Factory\InterpreterEntityListenerFactory::class            
        ],
        'aliases' => [
            'acl' => Service\Acl::class,
            'interpreter-listener' => Listener\InterpreterEntityListener::class,
        ],
    ],
];
