<?php
/**
 * module/InterpretersOffice/config/module.config.php.
 */

namespace InterpretersOffice;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            ///*
            'home' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/',
                    'defaults' => [
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
                        'controller' => Controller\AuthController::class,
                        'action' => 'logout',
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

    'view_helper_config' => array(
        'flashmessenger' => array(
            'message_open_format' => '<div%s><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><p>',
            'message_separator_string' => '</p><p>',
            'message_close_string' => '</p></div>',
        ),
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
           Controller\AuthController::class => Controller\Factory\AuthControllerFactory::class,
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
    ],
    'doctrine' => [

        'driver' => array(
            // defines an annotation driver with two paths, and names it `my_annotation_driver`
            'application_annotation_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(
                    __DIR__.'/../src/Entity',
                ),
            ),

            // default metadata driver, aggregates all other drivers into a single one.
            // Override `orm_default` only if you know what you're doing
            'orm_default' => array(
                'drivers' => array(
                    // register `my_annotation_driver` for any entity under namespace `My\Namespace`
                    __NAMESPACE__.'\Entity' => 'application_annotation_driver',
                ),
            ),
        ),
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
        ],

   ],

];
