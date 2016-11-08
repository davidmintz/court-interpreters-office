<?php
/**
 * module/Application/config/module.config.php
 */

namespace Application;
use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
//use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            ///*
            'home' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            // based on one that comes out of the box with the 
            // Skeleton Application; no reason not to remove it at some point.
            'application' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/app[/:action]',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            // can't get this to work, an effort to make multiple controllers
            // match this route
            /*
            'example' => [
                
                'type' => Segment::class,
                'options' => [
                    'route' => '/admin/:controller[/:action]',
                    'constraints' => [
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]+',
                    ],
                    'defaults' => [
                        'controller' => 'Application\Controller\IndexController',
                        'action'     => 'index',
                    ],
                ],
                
             ],*/
            'locations' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [

                    'route' => '/admin/locations[/:action[/:id]]',
                    'defaults' => [
                        'controller' => Controller\LocationsController::class,
                        'action'     => 'index',
                    ],
                ],
                 'constraints' => [
                    'action' => 'add|edit|index|list|delete',
                     'id' => '[1-9]\d*'
                ],
            ],
            'languages' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route'    => '/admin/languages',
                    'defaults' => [
                        'controller' => Controller\LanguagesController::class,
                        'action'     => 'index',
                    ],
                ],
                'child_routes' => [
                    'add' => [
                        'type' => Segment::class,
                        'options' => [
                            'route'    => '/add',
                            'defaults' => [
                                'action' => 'add',
                            ],
                        ],
                    ],
                    'edit' => [
                        'type' => Segment::class,
                        'options' => [
                            'route'    => '/edit/:id',
                            'defaults' => [
                                'action' => 'edit',

                            ],
                            'constraints' => [
                                'id' => '[1-9]\d*',
                            ],
                        ],
                    ],
                    'delete' => [
                        'type' => Segment::class,
                        'options' => [
                            'route'    => '/delete/:id',
                            'defaults' => [
                                'action' => 'delete',
                                
                            ],
                            'constraints' => [
                                'id' => '[1-9]\d*',
                            ],
                        ],
                    ],
                ],
            ],
           
           /*
            'languages' => [
           
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route'    => '/admin/languages[/:action[/:id]]',
                    'defaults' => [
                        'controller' => Controller\LanguagesController::class,
                        'action'     => 'index',
                        'id'         => null,
                    ],
                ],
                'constraints' => [
                    'action' => 'add|edit|list|delete',
                     'id' => '[1-9]\d*'
                ],
            ]
            */
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'formElementErrors'=> 'Application\Form\View\Helper\FormElementErrors',
        ]
    ],
    
    'view_helper_config' => array(
        'flashmessenger' => array(
            'message_open_format'      => '<div%s><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><p>',
            'message_separator_string' => '</p><p>',
            'message_close_string'     => '</p></div>',
        )
    ),
    'form_elements' => [
        'factories' => [
            Entity\Language::class => Form\Factory\AnnotatedEntityFormFactory::class,
            Entity\Location::class => Form\Factory\AnnotatedEntityFormFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
           Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
           Controller\LanguagesController::class => Controller\Factory\SimpleEntityControllerFactory::class,
           Controller\LocationsController::class => Controller\Factory\SimpleEntityControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'doctrine' => [

        'driver' => array(
            // defines an annotation driver with two paths, and names it `my_annotation_driver`
            'application_annotation_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(
                    __DIR__ . '/../src/Entity',  
                ),
            ),

            // default metadata driver, aggregates all other drivers into a single one.
            // Override `orm_default` only if you know what you're doing
            'orm_default' => array(
                'drivers' => array(
                    // register `my_annotation_driver` for any entity under namespace `My\Namespace`
                    __NAMESPACE__ . '\Entity' => 'application_annotation_driver',
                )
            )
        )
   ],
   'service_manager' => [
        'aliases' =>[
          'entity-manager' =>  'doctrine.entitymanager.orm_default',
        ],
   ],
   
];
