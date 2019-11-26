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
            Controller\RestRotationController::class => Controller\IndexController::class,
        ],
        // 'deny' => [
        //     'staff' => [
        //         Controller\RestRotationController::class => ['update','delete','create'],
        //         //Controller\IndexController::class => null,
        //     ],
        // ],
        'allow' => [
            'manager' => [
                Controller\IndexController::class => ['index','view'],
                Controller\RestRotationController::class => ['get'],
            ],
            'staff' => [
                Controller\IndexController::class => ['index','view'],
                Controller\RestRotationController::class => ['get'],
            ],
        ],

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
                'may_terminate' => false,
                'options' => [
                    'route'=>'/admin/rotations/assignments',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\RestRotationController::class,

                    ],
                ],
                'child_routes' => [
                    'get'=> [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options'=> [
                            'route' => '/:date/:id',
                            'defaults' => [
                                'constraints' => [
                                    'id' => '[1-9]\d*',
                                    'date' => 'd{4}-\d\d-\d\d',
                                ],
                            ],
                        ],
                    ],
                    'post' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options'=> [
                            'route' => '/create',
                        ],
                    ],
                    'put' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options'=> [
                            'route' => '/update/:id',
                            'constraints' => [
                                'id' => '[1-9]\d*',
                            ],
                        ],
                    ],
                    'delete' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options'=> [
                            'route' => '/delete/:id',
                            'constraints' => [
                                'id' => '[1-9]\d*',
                            ],
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
