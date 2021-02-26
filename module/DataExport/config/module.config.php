<?php 

namespace SDNY\DataExport;

use Laminas\Router\Http\Segment;

return [
    'controllers' => [
        'factories' => [
            Controller\DataExportController::class => function($s){ return new Controller\DataExportController();},
        ],
    ],
    'router' => [
        'routes' => [
            'data-export' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route'=>'/admin/data-export',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\DataExportController::class,
                        'action' => 'index',
                    ],
                ],
            ],
        ],
    ],
    'acl' => [
        'resources' => [
            Controller\DataExportController::class => null,
        ],

        'allow' => [
            'manager' => [
                // resource (controller) => privileges (actions)
                Controller\DataExportController::class => ['index',],
                
            ],
            // 'staff' => [
            //     Controller\IndexController::class => ['index','view'],
            //     Controller\RestRotationController::class => ['get'],
            // ],
        ],
    ],
    'navigation' => [
        'default'  => [
            'tools' => [
                'pages' => [
                    [
                        'order' => 30,
                        'label' => 'data export',
                        'route' => 'data-export',
                        'title' => 'yadda yadda',
                        'route_matches' => ['data-export'],
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
                        'label' => 'data export',
                        'route' => 'data-export',                       
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_map' => include(__DIR__.'/template_map.php'),
        // 'template_path_stack' => [ __DIR__.'/../view', ],
    ],
    
];