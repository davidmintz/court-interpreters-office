<?php
namespace InterpretersOffice\Admin\Rotation;
use Laminas\Router\Http\Segment;
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
            Controller\RestTaskController::class => Controller\Factory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Service\TaskRotationService::class => Service\TaskRotationServiceFactory::class,
        ],
    ],
    /*
        permissions for Rotation module
    */
    'acl' => [
        'resources' => [
            Controller\IndexController::class => null, //'InterpretersOffice\Admin\Controller\EventsController',
            Controller\RestRotationController::class => null, //Controller\IndexController::class,
            Controller\RestTaskController::class => null, //Controller\IndexController::class,
        ],

        'allow' => [
            'manager' => [
                // resource (controller) => privileges (actions)
                Controller\IndexController::class => ['index','view'],
                /*
                to allow the "manager" role to create Rotation and/or Task
                entities, add string 'create-rotation' and/or 'create-task'
                to the array below
                */
                Controller\RestRotationController::class => ['get','create-substitution'],
            ],
            'staff' => [
                Controller\IndexController::class => ['index','view'],
                Controller\RestRotationController::class => ['get'],
            ],
        ],
    ],
    'navigation' => [
        'default' => [
            'admin' => [
                 'pages' => [
                    [
                        'order' => 4000,
                        'label' => 'task rotations',
                        'route' => 'rotations',
                        'title' => 'management of Interpreters\' rotating tasks',
                        'route_matches' => ['rotations'],
                     ],
                 ],
            ],
        ],

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
                                'label' => 'create task',
                                'route' => 'rotations/create'
                            ],
                            [
                                'label' => 'create',
                                'route' => 'rotations/create_rotation'
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'restful_tasks' => [
                'type' => Segment::class,
                'may_terminate' => false,
                'options' => [
                    'route'=>'/admin/tasks',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\RestTaskController::class,
                    ],

                ],
                'child_routes' => [
                    'post' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options'=> [
                            'route' => '/create',
                        ],
                    ],
                ],
            ],
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
                    'substitute' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options'=> [
                            'route' => '/substitute',
                            'defaults' => [
                                'action' => 'create-substitution',
                            ],
                        ],
                    ],
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
                    'create_rotation' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '[/task/:task_id]/create',
                            'defaults' => [
                                'action' => 'create-rotation',
                                'constraints' => [
                                    'task_id' => '[1-9]\d*',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_map' => include(__DIR__.'/template_map.php'),
        'template_path_stack' => [ __DIR__.'/../view', ],
    ],
];
