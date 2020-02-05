<?php
/**
 * module/InterpretersOffice/Requests/config/module.config.php.
 */

namespace InterpretersOffice\Requests;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

use InterpretersOffice\Requests\Controller\Admin;

//$environment = getenv('environment') ?: 'development';

// set to 'array' to disable
//$doctrine_cache = $environment == 'testing' ? 'array' : 'filesystem';
//$

$event_configuration_file = file_exists(
    __DIR__.'/custom.event-listeners.json') ?
    __DIR__.'/custom.event-listeners.json' :
    __DIR__.'/default.event-listeners.json';


return [
    /** @todo move 'event_listeners' to a config/autoload that reads the JSON config */
    // for configuring the behavior of ScheduleUpdateManager
    'event_listeners' => json_decode(file_get_contents($event_configuration_file),true),

    'controllers' => [
        'factories' => [
           Controller\IndexController::class => Controller\Factory\RequestsControllerFactory::class,
           Controller\WriteController::class => Controller\Factory\RequestsControllerFactory::class,
           Controller\Admin\IndexController::class => Controller\Factory\RequestsControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_map' => include(__DIR__.'/template_map.php'),
        'template_path_stack' => [
            __DIR__.'/../view',
        ],
    ],

    'service_manager' => [
        'factories' => [
            Entity\Listener\RequestEntityListener::class =>
                Entity\Listener\Factory\RequestEntityListenerFactory::class,
        ],

    ],
    // add the entity path to the doctrine config
    'doctrine' => [
        'driver' => [
            'application_annotation_driver' => [
                'paths' => [
                    __DIR__.'/../src/Entity',
                ],
            ],
            'orm_default' => [
                'drivers' => [
                    __NAMESPACE__.'\Entity' => 'application_annotation_driver',
                ],
            ],
        ],
    ],

    // work in progress
    'navigation' => [
        'admin_breadcrumbs' =>  [
            [
                'label' => 'admin',
                'route' => 'admin',
                'pages' => [
                    [
                        'label' => 'requests',
                        'route' => 'admin-requests',
                        'expand' => false,
                        'pages' => [
                            // [
                            //     'label'=>'configuration',
                            //     'route'=>'admin-requests/config',
                            // ],
                            [
                                'label' => 'view details',
                                'route' => 'admin-requests/view',
                                'expand' => false,
                            ],
                        ],
                    ],
                    // see what would happens if we move this to the Requests config
                    'configuration' =>[
                        'label' => 'configuration',
                        'route' => 'configuration',
                        'pages' => [
                            [
                                'label' => 'requests',
                                'route' => 'configuration/requests',
                            ]
                        ],
                    ],
                ],
            ],

        ],
        'RequestsBreadcrumbs' =>
        [
            [
                'label' => 'requests',
                'route' => 'requests',
                'pages' => [
                    [
                        'route' => 'requests/list',
                        'label' => 'list',
                        'pages' => [
                            [
                                'route' => 'requests/update',
                                'label' => 'update',
                            ],
                            [
                                'route' => 'requests/view',
                                'label' => 'view details',
                            ],
                        ],
                    ],
                    [
                        'route' => 'requests/create',
                        'label' => 'create',
                    ],
                    [
                        'route' => 'requests/search',
                        'label' => 'search',
                    ],
                    // [
                    //     'route' => 'requests/update',
                    //     'label' => 'update',
                    // ],

                    [
                        'route' => 'requests/help',
                        'label' => 'help',
                    ]
                ],
            ],
        ],
        // navigation menu
        'requests' => [
            [
                'label' => 'requests',
                'route' => 'requests',
                'title' => 'main page for scheduling interp'
            ],
            [
                'label' => 'list',
                'route' => 'requests/list',
                'route_matches' => ['requests/list'],
                'title' => 'display requests for interpreting services'
            ],
            [
                'route' => 'requests/create',
                'route_matches' => ['requests/create'],
                'label' => 'create',
                'title' => 'schedule an interpreter'
            ],
            [
                'route' => 'requests/search',
                'label' => 'search',
                'route_matches' => ['requests/search'],
                'title' => 'search for past interpreter requests',
                'pages' => [
                    [
                        'label' => 'shit',
                        'title' => 'quick search',
                        'route' => 'requests/search',
                    ],
                    [
                        'label' => 'more options',
                        'title' => 'advanced search options',
                        'route' => 'requests/search',
                    ],
                ]
            ],
            [
                'route' => 'requests/help',
                'route_matches' => ['requests/help'],
                'label' => 'help',
                'title' => 'get help with this application'
            ],

            //*
        ],
        // a/k/a admin
        'default' => [
            [
                'label' => 'requests',
                'route' => 'admin-requests',
                'route_matches' => ['admin-requests',],
                'title' =>  'manage incoming requests',
                'order' => 10,
                'expand' => false,
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'defendants' => [
                'type' => Literal::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/defendants/validate',
                    'defaults' => [
                        'module' => 'InterpretersOffice',
                        'controller' => 'InterpretersOffice\Controller\DefendantsController',
                        'action' => 'validate',
                    ],
                ]
            ],

            'admin-requests' => [
                'type' => Literal::class,
                'may_terminate' => true,
                'options'=>[
                    'route' => '/admin/requests',
                    'defaults' => [
                        'module' => 'InterpretersOffice\Admin',
                        'controller' => Admin\IndexController::class,
                        'action' => 'index',
                    ],
                ],
                'child_routes' => [
                    // read-only, in effect
                    'config' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/config',
                            'defaults' => [
                                'controller' => Admin\IndexController::class,
                                'action'   => 'config',
                            ],
                        ],
                    ],
                    'update-config' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/update-config',
                            'defaults' => [
                                'controller' => Admin\IndexController::class,
                                'action'   => 'updateConfig',
                            ],
                        ],
                    ],

                    'scheduled' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/scheduled',
                            'defaults' => [
                                //'controller' => Admin\IndexController::class,
                                'action'   => 'get-scheduled',

                            ],
                        ],
                    ],
                    'view' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/:action/:id',
                            'defaults' => [
                                'controller' => Admin\IndexController::class,
                                'action'   => 'view',
                                'constraints' => [
                                    'action' => 'view|schedule',
                                    'id' => '[1-9]\d*',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'requests' => [
                'type' => Literal::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/requests',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\IndexController::class,
                        'action' => 'index',
                    ],
                ],
                'child_routes' => [
                    'list' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/list',
                            'defaults' => [
                                //  'controller' => Controller\IndexController::class,
                                'action' => 'list',
                            ],
                        ],
                    ],
                    'create' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/create[/repeat/:id]',
                            'defaults' => [
                                'controller' => Controller\WriteController::class,
                                'action' => 'create',
                            ],
                            'constraints' => [
                                'id' => '[1-9]\d*',
                            ],
                        ],
                    ],
                    'view' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/view/:id',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action' => 'view',
                            ],
                            'constraints' => [
                                'id' => '[1-9]\d*',
                            ],
                        ],
                    ],
                    'search' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/search',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action' => 'search',
                            ],
                        ],
                        'child_routes' => [
                            'docket' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/docket/:docket',
                                    'defaults' => ['action'=>'docket-search'],
                                    'constraints'=>[ 'docket' => '\d{4}-([A-Z]|[a-z]){2,4}-\d{3,5}']
                                ],
                            ],
                        ],
                    ],
                    'help' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/help',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action' => 'help',
                            ],
                        ],
                    ],
                    'cancel' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/cancel/:id',
                            'defaults' => [
                                'controller' => Controller\WriteController::class,
                                'action' => 'cancel',
                            ],
                            'constraints' => [
                                'id' => '[1-9]\d*',
                            ],
                        ],
                    ],
                    'update' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/update/:id',
                            'defaults' => [
                                'controller' => Controller\WriteController::class,
                                'action' => 'update',
                            ],
                            'constraints' => [
                                'id' => '[1-9]\d*',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'acl' => [
        'resources' => [
            'InterpretersOffice\Requests\Controller\Admin\IndexController' =>
            'InterpretersOffice\Admin\Controller\LanguagesController',
        ],
        'allow' => [
            'staff' => [
                'InterpretersOffice\Requests\Controller\Admin\IndexController' => ['config'],
            ],
        ],
        'deny' => [
            'manager' => [
                'InterpretersOffice\Requests\Controller\Admin\IndexController' => ['updateConfig'],
            ],
        ],
    ],

    'view_helpers' => [
        'invokables' => [
            'configCheckbox' => 'InterpretersOffice\Requests\View\Helper\ConfigCheckbox',
        ],
    ],
];
