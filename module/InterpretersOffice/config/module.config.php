<?php
/**
 * module/InterpretersOffice/config/module.config.php.
 */

namespace InterpretersOffice;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;
use InterpretersOffice\View\Helper as ViewHelper;
use InterpretersOffice\Service;


$environment = getenv('environment') ?: 'development';

// set to 'array' to disable
$doctrine_cache = $environment == 'testing' ? 'array' : 'filesystem';

return [
    'form_elements' => [
        'factories' => [
            Form\PersonForm::class => Form\Factory\PersonFormFactory::class,            
        ],
    ],
    'controllers' => [
        'factories' => [
           Controller\AuthController::class => Controller\Factory\AuthControllerFactory::class,
           Controller\AccountController::class => Controller\Factory\AccountControllerFactory::class,
           Controller\LocationsController::class => Controller\Factory\LocationsControllerFactory::class,
           Controller\DefendantsController::class => Controller\Factory\DefendantsControllerFactory::class,
           Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
        ],       
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions' => false,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => [
            'layout/layout' => __DIR__.'/../view/layout/layout.phtml',
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
        // 'cache' => [
        //     'redis' => [
        //         'namespace' => 'Doctrine',
        //         'instance' => 'Service\Cache\Redis',
        //     ],
        // ],
        // 'configuration' => [
        //      'orm_default' => [
        //         'query_cache' => 'phpfilecache',//function($c) {return new \Doctrine\Common\Cache\PhpFileCache('data/cache');},
        //         'result_cache' => 'redis',
        //         'metadata_cache' => 'phpfilecache',
        //         'hydration_cache' => 'phpfilecache',
        //      ],
        // ],
    ],

    'service_manager' => [
        'aliases' => [
          'entity-manager' => 'doctrine.entitymanager.orm_default',
          'auth' => 'Laminas\Authentication\AuthenticationService',
          'log' => \Laminas\Log\Logger::class,
        ],
        'factories' => [
            'Service\Cache\Redis'=> Service\Factory\RedisFactory::class, //function($container){$redis = new \Redis(); $redis->connect("localhost"); return $redis; },
            'Laminas\Authentication\AuthenticationService' => 'InterpretersOffice\Service\Factory\AuthenticationFactory',
            'annotated-form-factory' => 'InterpretersOffice\Form\Factory\AnnotatedEntityFormFactory',
            \Laminas\Log\Logger::class => Service\Factory\LogFactory::class,
            Service\Listener\AuthenticationListener::class => Service\Factory\AuthenticationListenerFactory::class,
            Entity\Listener\UpdateListener::class => Entity\Listener\Factory\UpdateListenerFactory::class,           
            Service\AccountManager::class => Service\Factory\AccountManagerFactory::class,
            'doctrine.cache.phpfilecache' => Service\Factory\DoctrinePhpFileCacheFactory::class,

        ],
        'abstract_factories' => [
            \Laminas\Navigation\Service\NavigationAbstractServiceFactory::class,
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
            'schedule' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/schedule[/:year/:month/:date[/:language]]',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\IndexController::class,
                        'action' => 'schedule',                         
                    ],
                    'constraints' => [
                        'shit' => '',
                        'year' => '[12]\d\d\d',
                        'month' => '\d\d',
                        'date' => '\d\d',
                        'language'=>'(non-)?[Ss]panish|all',
                        
                    ],
                ],                
            ],
            'view-event' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/schedule/view/:id',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\IndexController::class,
                        'action' => 'view-event',                         
                    ],
                    'constraints' => [                  
                        'id' => '[1-9][0-9]+',                    
                    ],              
                ],
            ],
            'contact' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/contact',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\IndexController::class,
                        'action' => 'contact',
                    ],
                ],
            ],
            'public-locations' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/locations',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\LocationsController::class,
                        'action' => 'index',
                    ],
                ],
                'child_routes' => [
                    'get_children' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/get-children',
                            'defaults' => [
                                'action' => 'getChildren',
                            ],
                        ],
                    ],

                ],
            ],
            'login' => [
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
                'may_terminate' => false,
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
                    'validate' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/register/validate',
                            'defaults' => [
                                'action' => 'validate',
                            ],
                        ],
                    ],
                    // to do: experiment with collapsing the ones
                    // with no url params into one config array
                    'verify-email' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/verify-email/:id/:token',
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

                            'route' => '/reset-password/:id/:token',
                            'defaults' => [
                                'action' => 'reset-password',
                            ],
                        ],
                    ],
                    'edit-profile' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/profile',
                            'defaults' => [
                                'action' => 'edit',
                            ],
                        ],
                    ],
                ],
            ],
            'defendant-autocomplete' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/defendants/autocomplete',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\DefendantsController::class,
                        'action' => 'autocomplete',
                    ],
                ],
            ],
            'defendant-render' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/defendants/render',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\DefendantsController::class,
                        'action' => 'render',
                    ],
                ],
            ],
            'defendant-search' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/defendants/search',
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\DefendantsController::class,
                        'action' => 'search',
                    ],
                ],
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

        'aliases' => [
            'defendants' => ViewHelper\Defendants::class,
            'interpreters' => ViewHelper\InterpreterNames::class,
            'errorMessage' => ViewHelper\ErrorMessage::class,
            'successMessage' => ViewHelper\SuccessMessage::class,
            'diff' => ViewHelper\Diff::class,
            'dateTime' => ViewHelper\DateTime::class,
            'parsedown' => ViewHelper\Parsedown::class,
        ],
        'factories' => [
            ViewHelper\Defendants::class => InvokableFactory::class,
            ViewHelper\ErrorMessage::class => InvokableFactory::class,
            ViewHelper\SuccessMessage::class => InvokableFactory::class,
            ViewHelper\InterpreterNames::class => InvokableFactory::class,
            ViewHelper\Diff::class => InvokableFactory::class,
            ViewHelper\DateTime::class => InvokableFactory::class,
            ViewHelper\Parsedown::class => InvokableFactory::class,
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
