<?php
namespace InterpretersOffice\Admin\Rotation;
use Zend\Router\Http\Segment;
return [

    'rotations' => [
        'enabled' => true,
    ],
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
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => Controller\Factory::class,
            Controller\RestRotationController::class => Controller\Factory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Service\TaskRotationService::class => Service\TaskRotationServiceFactory::class,
        ],
    ],
    'acl' => [
        'resources' => [
            Controller\IndexController::class => 'InterpretersOffice\Admin\Controller\EventsController',
            Controller\RestRotationController::class => null,
        ],
        'deny' => [
            'manager' => [
                Controller\RestRotationController::class => null,
                Controller\IndexController::class => null,
            ],
        ],
        'allow' => [
            'manager' => [
                Controller\IndexController::class => ['index','view'],
            ]
        ]
    ],
    'navigation' => [
        'admin_breadcrumbs' => [
            [
                'label' => 'admin',
                'route' => 'admin',
                'pages' => [
                    [
                        'label' => 'task rotations',
                        'route' => 'rotations',
                        'pages' => [
                            [
                                'label' => '',
                                'route' => 'rotations/view'
                            ],
                            [
                                'label' => 'create',
                                'route' => 'task/create'
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'restful_rotations' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route'=>'/admin/rotations/assignments/:date/:id',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\RestRotationController::class,
                        'constraints' => [
                            'id' => '[1-9]\d*',
                            'date' => 'd{4}-\d\d-\d\d',
                        ],
                    ],
                ],
            ],
            'rotations' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route'=>'/admin/rotations',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\IndexController::class,
                        'action'=>'index',
                    ],
                ],
                'child_routes' => [
                    'view' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/view/:id',
                            'defaults' => [
                                'action' => 'view',
                                'constraints' => [
                                    'id' => '[1-9]\d*',
                                ],
                            ],
                        ],
                    ],
                    // for the form presentation
                    'create' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/task/create',
                            'defaults' => [
                                'action' => 'create-task',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_map' => include(__DIR__.'/template_map.php'),
        //'template_path_stack' => [ __DIR__.'/../view', ],
    ],
];
