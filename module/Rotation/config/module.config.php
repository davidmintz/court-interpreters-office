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
            Controller\IndexController::class => Controller\IndexControllerFactory::class,
        ],
    ],
    'acl' => [
        'resources' => [
            Controller\IndexController::class => 'InterpretersOffice\Admin\Controller\EventsController',
        ],
    ],
    'router' => [
        'routes' => [
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
            ],
        ],
    ],
    'view_manager' => [
        'template_map' => include(__DIR__.'/template_map.php'),
        'template_path_stack' => [
            __DIR__.'/../view',
        ],
    ],
];
