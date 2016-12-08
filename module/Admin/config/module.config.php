<?php 
namespace InterpretersOffice\Admin;
use Zend\Router\Http\Segment;

return [
    
    'router'=>[
        'routes' => [
            
            'test' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/admin/test',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\IndexController::class,
                        'action' => 'test',
                    ],
                ],
            ],
            'languages' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/admin/languages',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\LanguagesController::class,
                        'action' => 'index',
                    ],
                ],
                'child_routes' => [
                    'add' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/add',
                            'defaults' => [
                                'action' => 'add',
                            ],
                        ],
                    ],
                    'edit' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/:action/:id',
                            'defaults' => [
                                'action' => 'edit',

                            ],
                            'constraints' => [
                                'action' => 'edit|delete',
                                'id' => '[1-9]\d*',
                            ],
                        ],
                    ], 
                ],
            ],
            'locations' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [

                    'route' => '/admin/locations',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\LocationsController::class,
                        'action' => 'index',
                    ],
                ],
                'child_routes' => [
                    'type'=> [ 
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/type/:id',
                            'defaults' => [
                                'action' => 'index'
                            ],
                            'constraints' => [
                                'id' => '[1-9]\d*',
                            ],
                        ],
                    ],
                    'add' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/add',
                            'defaults' => [
                                'action' => 'add',
                            ],
                        ],
                    ],
                    'edit' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/:action/:id',
                            'defaults' => [
                                'action' => 'edit',

                            ],
                            'constraints' => [
                                'action' => 'edit|delete',
                                'id' => '[1-9]\d*',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        
        'invokables'  => [
            Controller\IndexController::class => Controller\IndexController::class,
        ],
        'factories' => [
            Controller\LanguagesController::class => \InterpretersOffice\Controller\Factory\SimpleEntityControllerFactory::class,
            Controller\LocationsController::class => \InterpretersOffice\Controller\Factory\SimpleEntityControllerFactory::class,
        ],
    ],
    'view_manager' => [
      
        'template_path_stack' => [
            'interpreters-office/admin' => __DIR__ . '/../view',
        ],
    ],
    
];
