<?php
/**
 * module/InterpretersOffice/Requests/config/module.config.php.
 */

namespace InterpretersOffice\Requests;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;

return [
    'controllers' => [
        'factories' => [
           Controller\RequestsIndexController::class => Controller\Factory\RequestsIndexControllerFactory::class,
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
    'router' => [
        'routes' => [
            
            'requests' => [
                'type' => Literal::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/requests',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\RequestsIndexController::class,
                        'action' => 'index',
                    ],
                ],
            ],
        ],
    ],    
];
