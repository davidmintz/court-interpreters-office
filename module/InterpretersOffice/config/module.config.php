<?php
/**
 * module/InterpretersOffice/config/module.config.php.
 */

namespace InterpretersOffice;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;

$environment = getenv('APP_ENV') ?: 'development';

// set to 'array' to disable
$doctrine_cache = $environment == 'testing' ? 'array' : 'filesystem';

return [
    'form_elements' => [
        'factories' => [
            Entity\Language::class => Form\Factory\AnnotatedEntityFormFactory::class,
            Entity\Location::class => Form\Factory\AnnotatedEntityFormFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
           Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
           Controller\AuthController::class => Controller\Factory\AuthControllerFactory::class,
           Controller\AccountController::class => Controller\Factory\AccountControllerFactory::class,
           Controller\ExampleController::class => Controller\Factory\ExampleControllerFactory::class,
        ],
        'invokables' => [
            //Controller\ExampleController::class => Controller\ExampleController::class,
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => [
            'layout/layout' => __DIR__.'/../view/layout/layout.phtml',
            // maybe remove this next line?
            'application/index/index' => __DIR__.'/../view/application/index/index.phtml',
            'error/404' => __DIR__.'/../view/error/404.phtml',
            'error/index' => __DIR__.'/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__.'/../view',
        ],
        // http://stackoverflow.com/questions/18014885/how-to-disable-layout-and-view-renderer-in-zf2
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
    'doctrine' => [
        'eventmanager' => [
            'orm_default' => [
                'subscribers' => [
                    Entity\Listener\UpdateListener::class,
                ],
            ],
        ],  
        'driver' => [
            // defines an annotation driver with one path, and names it `my_annotation_driver`
            'application_annotation_driver' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [
                    __DIR__.'/../src/Entity',
                ],
            ],

            // default metadata driver, aggregates all other drivers into a single one.
            // Override `orm_default` only if you know what you're doing
            'orm_default' => [
                'drivers' => [
                    // register `my_annotation_driver` for any entity under namespace `My\Namespace`
                    __NAMESPACE__.'\Entity' => 'application_annotation_driver',
                ],                
            ],
        ],
        'configuration' => [
             'orm_default' => [
                'query_cache' => $doctrine_cache,
                'result_cache' => $doctrine_cache,
                'metadata_cache' => $doctrine_cache,
                'hydration_cache' => $doctrine_cache,
            ],            
        ],        
    ],

    'service_manager' => [
        'aliases' => [
          'entity-manager' => 'doctrine.entitymanager.orm_default',
          'auth' => 'Zend\Authentication\AuthenticationService',
          'log' => \Zend\Log\Logger::class,
        ],
        'factories' => [
            'Zend\Authentication\AuthenticationService' => 'InterpretersOffice\Service\Factory\AuthenticationFactory',
            'annotated-form-factory' => 'InterpretersOffice\Form\Factory\AnnotatedEntityFormFactory',
            \Zend\Log\Logger::class => Service\Factory\LogFactory::class,
            Service\Listener\AuthenticationListener::class => Service\Factory\AuthenticationListenerFactory::class,
            Entity\Listener\UpdateListener::class => Entity\Listener\Factory\UpdateListenerFactory::class,
            Form\PersonForm::class => Form\Factory\PersonFormFactory::class,            
        ],

    ],
    'session_containers' => [
        'Authentication',
    ],

    'router' => [
        'routes' => [
            ///*
            'home' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\IndexController::class,
                        'action' => 'index',
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
                        'controller' => 'InterpretersOffice\Controller\IndexController',
                        'action'     => 'index',
                    ],
                ],

             ],*/
            // based on one that comes out of the box with the
            // Skeleton Application; no reason not to remove it at some point.
            'application' => [
                'type' => Segment::class,
                'options' => [
                     'module' => __NAMESPACE__,
                    'route' => '/app[/:action]',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'auth' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/login',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\AuthController::class,
                        'action' => 'login',
                    ],
                ],
                // to be continued
            ],
            'logout' => [
                'type' => Literal::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/logout',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\AuthController::class,
                        'action' => 'logout',
                    ],
                ],
            ],
            'account' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/user',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\AccountController::class,
                        'action' => 'index',
                    ],
                ],
                'child_routes' => [
                    'register' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/register',
                            'defaults' => [
                                'action' => 'register',
                            ],
                        ],
                    ],
                    // to do: experiment with collapsing the ones
                    // with no url params into one config array
                    'verify-email' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/verify-email',
                            'defaults' => [
                                'action' => 'verifyEmail',
                            ],
                        ],
                    ],
                    'request-password' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/request-password',
                            'defaults' => [
                                'action' => 'requestPassword',
                            ],
                        ],
                    ],
                    'reset-password' => [
                        'type' => Segment::class,
                        'options' => [
                            // to do: add parameters 
                            'route' => '/reset-password',
                            'defaults' => [
                                'action' => 'reset-password',
                            ],
                        ],
                    ],

                ],
                // to be continued
            ],
            'example' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/example[/:action]',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\ExampleController::class,
                        'action' => 'index',
                    ],
                ],

            ],
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'formElementErrors' => 'InterpretersOffice\Form\View\Helper\FormElementErrors',
        ],
    ],

    'view_helper_config' => [
        'flashmessenger' => [
            'message_open_format' => '<div%s><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><p>',
            'message_separator_string' => '</p><p>',
            'message_close_string' => '</p></div>',
        ],
    ],
];
