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
                    'get_for_date' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/date/:date:/:type',
                            'verb' => 'GET',
                            'defaults' => [
                                'action' => 'get-by-date',
                                'constraints' => [
                                    'date' => '^\d{4}-\d{2}-\d{2}$',
                                    'type' => 'mot[dw]',
                                ],
                            ],
                        ],
                    ],
                    'get' => [
                        'type' => Segment::class,
                        'options' => [
                            'verb' => 'GET',
                            'route' => '/:type/:id',
                            'defaults' => [
                                'controller' => Controller\NotesController::class,
                                'constraints' => [
                                    'id' => '[1-9]\d*',
                                    'type' => 'mot[dw]',
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
];
