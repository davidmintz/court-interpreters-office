<?php

/**
 * configuration for InterpretersOffice\Admin module.
 */

namespace InterpretersOffice\Admin;

use Zend\Router\Http\Segment;

return [

    'router' => [
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
                    'route' => '/admin/interpreters',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\InterpretersController::class,
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
        ],
    ],
    'controllers' => [

        'invokables' => [
            Controller\AdminIndexController::class => Controller\AdminIndexController::class,
        ],
        'factories' => [
            Controller\LanguagesController::class => Controller\Factory\SimpleEntityControllerFactory::class,
            Controller\LocationsController::class => Controller\Factory\SimpleEntityControllerFactory::class,
            Controller\EventTypesController::class => Controller\Factory\SimpleEntityControllerFactory::class,
            Controller\PeopleController::class => Controller\Factory\PeopleControllerFactory::class,
            Controller\JudgesController::class => Controller\Factory\PeopleControllerFactory::class,
            Controller\InterpretersController::class => Controller\Factory\PeopleControllerFactory::class,
            Controller\UsersController::class => Controller\Factory\PeopleControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_map' => [
            'interpreters-office/admin/admin-index/index' => __DIR__.'/../view/interpreters-office/admin/index/index.phtml',
        ],
        'template_path_stack' => [
            'interpreters-office/admin' => __DIR__.'/../view',
        ],
    ],
    // based on LearnZF2.
    // experimental, work in progress
    'acl' => [
        'roles' => [
            // 'role name' => 'parent role'
            'anonymous' => null,
            'submitter' => null,
            'manager'   => null,
            'administrator' => 'manager',
            'staff' => null,
        ],
        // some of this inheritance hierarchy might have to change
        'resources' => [
             // 'resource name' => 'parent resource'
            'languages' => null,
            'event-types'=> 'languages',            
            'locations'=>'languages',
            'events' => null,
            
            'users' => 'events',
            'people' => 'users',
           
            'judges' => 'events',
            'interpreters' => 'events',
            'requests' => null,
            'admin-index' => null,
            
            // to be continued
        ],
        // how to we configure this to use Assertions?
        // I think we don't
        'allow' => [
            //'role' => [ 'resource' => [ priv, other-priv, ...  ]
            'submitter' => [
                'requests' => ['create','view','index'],
                'events'   => ['index','view','search'],
            ],
            'manager' => [
                'admin-index' => null,
                'languages' => null,
                'events' => null,
            ],
            'staff' => [
                'admin-index' => ['index'],
            ],
            'administrator' => null,
        ],
        'deny' => [
            'administrator' => [
                'requests' => ['add','edit','update','delete','cancel'],
            ]
        ]
    ],
    'service_manager' => [
        'factories' => [
             Service\Acl::class  => Service\Factory\AclFactory::class,            
        ],
        'aliases' => [
            'acl' => Service\Acl::class,
        ],
    ],
];