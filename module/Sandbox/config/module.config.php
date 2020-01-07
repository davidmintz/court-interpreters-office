<?php

namespace Sandbox;

use Laminas\Router\Http\Segment;
use Laminas\Router\Http\Literal;

return [

    'controllers' => [
        'invokables' => [
            Controller\IndexController::class => Controller\IndexController::class,
        ],
    ],
    'router' => [

        'routes' => [
            'sandbox' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/sandbox',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\IndexController::class,
                        'action' => 'index',
                    ],
                ],
                'child_routes' => [
                    'shit' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/:action',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action' => 'one',
                            ],
                            'constraints' => [
                                'action' =>  'one|two|three',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            __DIR__.'/../view',
        ],
    ],

    'acl' => [
        'resources' => [
            Controller\IndexController::class => null,
        ],
    ],

];
