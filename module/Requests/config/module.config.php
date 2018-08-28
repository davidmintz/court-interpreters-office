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
    // experimental
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
        'requestsBreadcrumbs' => [
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
                ],
            ],
        ],
        // navigation menu
        'requests' => [
            [
                'label' => 'requests',
                'route' => 'requests',
            ],
            [
                'label' => 'list',
                'route' => 'requests/list'
            ],
            [
                'route' => 'requests/create',
                'label' => 'create',
            ],
            [
                'route' => 'requests/search',
                'label' => 'search',
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
                            'route' => '/create',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action' => 'test',
                            ],
                        ],
                    ],
                    'search' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/search',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action' => 'test',
                            ],
                        ],
                    ],
                    'update' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/update/:id',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action' => 'test',
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
