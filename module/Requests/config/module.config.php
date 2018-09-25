<?php
/**
 * module/InterpretersOffice/Requests/config/module.config.php.
 */

namespace InterpretersOffice\Requests;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
$environment = getenv('environment') ?: 'development';

// set to 'array' to disable
$doctrine_cache = $environment == 'testing' ? 'array' : 'filesystem';

return [
    'controllers' => [
        'factories' => [
           Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__.'/../view',
        ],
    ],

    'service_manager' => [
        'factories' => [
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
                'title' => 'main page for interpreter scheduling'
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
        ]
    ],
    'router' => [
        'routes' => [
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
                            'route' => '/list',//[/page/:page]',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action' => 'list',
                            ],
                            'constraints' => [
                                //'page' => '[1-9]\d*',
                            ],
                        ],
                    ],
                    'create' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/create',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action' => 'create',
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
                    'update' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/update/:id',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
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
    ]
];
