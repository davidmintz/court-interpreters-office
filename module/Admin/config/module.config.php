<?php
/**
 * module/Admin/config/module.config.php
 * configuration for InterpretersOffice\Admin module.
 */

namespace InterpretersOffice\Admin;

use InterpretersOffice\Entity\Listener;
use InterpretersOffice\Entity\Listener\InterpreterEventEntityListener;
use InterpretersOffice\Entity\Listener\Factory\InterpreterEntityListenerFactory;

return [

    'router' =>
        include __DIR__.'/routes.php'
    ,
    'controllers' => [

        'invokables' => [
            Controller\AdminIndexController::class => Controller\AdminIndexController::class,
        ],
        'factories' => [
            Controller\LanguagesController::class => Controller\Factory\BasicEntityControllerFactory::class,
            Controller\LocationsController::class => Controller\Factory\BasicEntityControllerFactory::class,
            Controller\EventTypesController::class => Controller\Factory\BasicEntityControllerFactory::class,
            Controller\PeopleController::class => Controller\Factory\PeopleControllerFactory::class,
            Controller\JudgesController::class => Controller\Factory\PeopleControllerFactory::class,
            Controller\InterpretersController::class => Controller\Factory\PeopleControllerFactory::class,
            Controller\InterpretersWriteController::class => Controller\Factory\PeopleControllerFactory::class,
            Controller\UsersController::class => Controller\Factory\UsersControllerFactory::class,
            Controller\EventsController::class => Controller\Factory\EventsControllerFactory::class,
            Controller\DefendantsController::class => Controller\Factory\DefendantsControllerFactory::class,
            Controller\ScheduleController::class => Controller\Factory\ScheduleControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_map' => [
            'interpreters-office/admin/admin-index/index' =>
                __DIR__.'/../view/interpreters-office/admin/index/index.phtml',
        ],
        'template_path_stack' => [
            'interpreters-office/admin' => __DIR__.'/../view',
        ],
    ],

    'acl' => include __DIR__.'/acl.php',

    'service_manager' => [
        'factories' => [
             Service\Acl::class  => Service\Factory\AclFactory::class,
             Listener\InterpreterEntityListener::class =>
                Listener\Factory\InterpreterEntityListenerFactory::class,
             Listener\EventEntityListener::class =>
                Listener\Factory\EventEntityListenerFactory::class,
            Listener\InterpreterEventEntityListener::class =>
                Listener\Factory\InterpreterEventEntityListenerFactory::class
        ],
        'aliases' => [
            'acl' => Service\Acl::class,
            'interpreter-listener' => Listener\InterpreterEntityListener::class,
            'adm_breadcrumbs' => 'Zend\Navigation\Admin_Breadcrumbs',
        ],
    ],
];
