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
                        'uri'   => 'gack',
                        'title' => 'view, edit, add, delete notes',
                    ],
                    [
                        'label' => 'daily',
                        'uri'   => 'gack',
                        'title' => 'toggle display of MOTD',
                    ],
                    [
                        'label' => 'weekly',
                        'uri'   => 'gack',
                        'title' => 'toggle display of MOTW',
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
             ]
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
                        //'action'=>'test',
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
                    ]
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
