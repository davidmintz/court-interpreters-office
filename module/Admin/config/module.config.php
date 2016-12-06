<?php 
namespace InterpretersOffice\Admin;
use Zend\Router\Http\Segment;

return [
    
    'router'=>[
        'routes' => [
            
            'test' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/admintest',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\IndexController::class,
                        'action' => 'test',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        
        'invokables'  => [
            Controller\IndexController::class => Controller\IndexController::class,
        ],
    ],
    
];
