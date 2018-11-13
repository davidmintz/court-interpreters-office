<?php
/**
 * module/InterpretersOffice/Requests/config/module.config.php.
 */

namespace InterpretersOffice\Requests;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;

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
    'controllers' => [
        'factories' => [
           Controller\IndexController::class => Controller\Factory\RequestsControllerFactory::class,
           Controller\WriteController::class => Controller\Factory\RequestsControllerFactory::class,
           Controller\Admin\IndexController::class => Controller\Factory\RequestsControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_map' => include(__DIR__.'/template_map.php')
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
                            [
                                'label'=>'configuration',
                                'route'=>'admin-requests/config',
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'RequestsBreadcrumbs' => [
            [
                'label' => 'requests',
                'route' => 'requests',
                'pages' => [
                    [
                        'route' => 'requests/list',
                        'label' => 'list',
                    ],
                    [
                        'route' => 'requests/create',
                        'label' => 'create',
                    ],
                    [
                        'route' => 'requests/search',
                        'label' => 'search',
                    ],
                    [
                        'route' => 'requests/update',
                        'label' => 'update',
                    ],
                    [
                        'route' => 'requests/view',
                        'label' => 'view details',
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
                'title' => 'display requests for interpreting services'
            ],
            [
                'route' => 'requests/create',
                'label' => 'create',
                'title' => 'schedule an interpreter'
            ],
            [
                'route' => 'requests/search',
                'label' => 'search',
                'title' => 'search for past interpreter requests'
            ],

            //*
        ],
        // a/k/a admin
        'default' => [
            [
                'label' => 'requests',
                'route' => 'admin-requests',
                'title' =>  'manage incoming requests',
                'order' => 200,
                'expand' => false,
            ]
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
            // work in progress -- expect it to blow up at first
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
                                'controller' => Controller\IndexController::class,
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
                                'constraints' => [
                                    'id' => '[1-9]\d*',
                                ],
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
                        'options' => [
                            'route' => '/search',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action' => 'search',
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
        ]
    ],
    'acl' => [
        'resources' => [
            'InterpretersOffice\Requests\Controller\Admin\IndexController' =>
            'InterpretersOffice\Admin\Controller\EventsController',

        ],
    ],

    'view_helpers' => [
        'invokables' => [
            'configCheckbox' => 'InterpretersOffice\Requests\View\Helper\ConfigCheckbox',
        ],
    ],
    'event_listeners' => [
        json_decode(file_get_contents($event_configuration_file))
    ]

];
