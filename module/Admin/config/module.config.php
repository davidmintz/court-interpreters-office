<?php
/**
 * module/Admin/config/module.config.php
 * configuration for InterpretersOffice\Admin module.
 */

namespace InterpretersOffice\Admin;

use InterpretersOffice\Entity\Listener;
use Laminas\ServiceManager\Factory\InvokableFactory;
use InterpretersOffice\Admin\Command\TestCommand;

return [

    'router' =>
        include __DIR__.'/routes.php'
    ,
    'controllers' => [

        'invokables' => [
            // Controller\IndexController::class => Controller\IndexController::class,
        ],
        'factories' => [
            Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
            Controller\LanguagesController::class => Controller\Factory\BasicEntityControllerFactory::class,
            Controller\LocationsController::class => Controller\Factory\BasicEntityControllerFactory::class,
            Controller\EventTypesController::class => Controller\Factory\BasicEntityControllerFactory::class,
            Controller\CourtClosingsController::class => Controller\Factory\BasicEntityControllerFactory::class,
            Controller\PeopleController::class => Controller\Factory\PeopleControllerFactory::class,
            Controller\JudgesController::class => Controller\Factory\PeopleControllerFactory::class,
            Controller\InterpretersController::class => Controller\Factory\PeopleControllerFactory::class,
            Controller\InterpretersWriteController::class => Controller\Factory\PeopleControllerFactory::class,
            Controller\UsersController::class => Controller\Factory\UsersControllerFactory::class,
            Controller\EventsController::class => Controller\Factory\EventsControllerFactory::class,
            Controller\DefendantsController::class => Controller\Factory\DefendantsControllerFactory::class,
            Controller\ScheduleController::class => Controller\Factory\ScheduleControllerFactory::class,
            Controller\EmailController::class => Controller\Factory\EmailControllerFactory::class,
            Controller\NormalizationController::class => Controller\Factory\NormalizationControllerFactory::class,
            Controller\SearchController::class => Controller\Factory\SearchControllerFactory::class,
            Controller\ReportsController::class => Controller\Factory\ReportsControllerFactory::class,
            Controller\ConfigController::class => Controller\Factory\ConfigControllerFactory::class,
            Controller\DocketAnnotationsController::class => Controller\Factory\DocketAnnotationsControllerFactory::class,
            Controller\RestfulDocketAnnotationsController::class => Controller\Factory\DocketAnnotationsControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_map' => include(__DIR__.'/template_map.php'),
        'template_path_stack' => [
            __DIR__.'/../view',
        ],
    ],
    // more of this in module/InterpretersOffice/config/module.config.php
    'view_helpers' => [
        'factories' => [
            View\Helper\EmailSalutation::class => InvokableFactory::class,
        ],
        'aliases' => [
            'salutation' => View\Helper\EmailSalutation::class,
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
                Listener\Factory\InterpreterEventEntityListenerFactory::class,
            Service\ScheduleUpdateManager::class =>  Service\Factory\ScheduleUpdateManagerFactory::class,
            Service\EmailService::class =>  Service\Factory\EmailServiceFactory::class,
            Service\Log\Writer::class =>  Service\Factory\DbLogWriterFactory::class,
            Form\InterpreterForm::class => Form\InterpreterFormFactory::class,
            Form\EventForm::class => Form\EventFormFactory::class,

            // a little learning exercise...
            // TestCommand::class =>  function($c)  { return new  TestCommand($c->get('entity-manager')); }

        ],
        'aliases' => [
            'acl' => Service\Acl::class,
            'interpreter-listener' => Listener\InterpreterEntityListener::class,
            'admin-breadcrumbs' => 'Laminas\Navigation\Admin_Breadcrumbs',
        ],
    ],
];
