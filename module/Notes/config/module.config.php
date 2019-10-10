<?php
namespace InterpretersOffice\Admin\Notes;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\Router\Http\Method;

return [
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
    'navigation' => [
        'default' => [
            [
                'label'=>'notes',
                'title'=> 'manage/view messages for the day (MOTD) or week (MOTW)',
                'uri' => '#',
                'order' => 100,
                'pages' => [
                    [
                        'label' => 'manage',
                        'uri'   => 'notes',
                        'title' => 'view, edit, add, delete notes',
                    ],
                    [
                        'label' => 'MOTD',
                        'uri'   => '#',
                        'title' => 'toggle display of MOTD',
                        'id'    => 'btn-motd',
                    ],
                    [
                        'label' => 'MOTW',
                        'uri'   => '#',
                        'title' => 'toggle display of MOTW',
                        'id' => 'btn-motw',
                    ],
                ]
            ],
            'tools' => [
                 'pages' => [
                     [
                         'label' => 'notes',
                         'uri' => '#',
                     ],
                 ],
             ],
        ],
        'admin_breadcrumbs' =>  [
            [
                'label' => 'admin',
                'route' => 'admin',
                'pages' => [
                    [
                        'label' => 'notes',
                        'route' => 'notes',
                        'expand' => false,
                        'pages' => [
                            [
                                'label'=>'edit',
                                'route'=>'notes/edit',
                            ],
                            [
                                'label' => 'create',
                                'route' => 'notes/create',
                                //'expand' => false,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'notes' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    // 'verb' => 'GET',
                    'route'=>'/admin/notes',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\NotesController::class,
                        //'action'=>'index',
                    ],
                ],
                /**
                *  interesting fact/note-to-self:
                *   with RestfulController, you don't specify the controller
                *   action because the framework does it for you
                */
                'child_routes' => [
                    // the "id" for these purposes is a date string YYYY-MM-DD
                    'get' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/date/:id[/:type]',
                            'defaults' => [
                                'constraints' => [
                                    'id' => '^\d{4}-\d{2}-\d{2}$',
                                    'type' => 'mot[dw]|all',
                                ],
                            ],
                        ],
                    ],
                    'put' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/update/:type/:id',
                            'defaults' => [
                                'constraints' => [
                                    'id' => '[1-9]\d*',
                                    'type' => 'mot[dw]',
                                ],
                            ],
                        ]
                    ],
                    // and this one is for the conventional id
                    'get_by_id' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/:type/id/:id',
                            'defaults' => [
                                'action' => 'get-by-id',
                                'controller' => Controller\NotesController::class,
                                'constraints' => [
                                    'id' => '[1-9]\d*',
                                    'type' => 'mot[dw]',
                                ],
                            ],
                        ],
                    ],
                    'settings' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/update-settings',
                            'defaults' => [
                                'action' => 'update-settings',

                            ],
                        ],
                    ],
                    'edit' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/edit/:type/:id/date/:date',
                            'defaults' => [
                                'action' => 'edit',
                                'constraints' => [
                                    'id' => '[1-9]\d*',
                                    'type' => 'mot[dw]',
                                    'date' =>  '^\d{4}-\d{2}-\d{2}$',
                                ],
                            ],
                        ],
                    ],
                    'create' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/create/:type/:date',
                            'defaults' => [
                                'action' => 'create',
                                'constraints' => [
                                    'type' => 'mot[dw]',
                                    'date' =>  '^\d{4}-\d{2}-\d{2}$',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\NotesController::class => Controller\NotesControllerFactory::class,
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
            Service\NotesService::class => Service\NotesServiceFactory::class,
        ],
    ],

];
