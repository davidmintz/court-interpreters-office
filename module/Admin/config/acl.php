<?php
/** module/Admin/config/acl.php
 *
 *  ACL configuration based on LearnZF2.
 */
use InterpretersOffice\Admin\Controller as Admin;
use InterpretersOffice\Controller as Main;
use InterpretersOffice\Requests\Controller as Requests;
use InterpretersOffice\Requests\Entity\Request;

return [
    'roles' => [
        // 'role name' => 'parent role'
        'anonymous' => null,
        'submitter' => null,
        'manager' => null,
        'administrator' => 'manager',
        'staff' => null,
    ],

    'resources' => [
        // 'resource name (controller)' => 'parent resource'
        Admin\LanguagesController::class => null,
        Admin\EventTypesController::class => Admin\LanguagesController::class,
        Admin\LocationsController::class => Admin\LanguagesController::class,
        Admin\EventsController::class => null,
        Admin\UsersController::class => Admin\EventsController::class,
        Admin\PeopleController::class => Admin\UsersController::class,
        Admin\JudgesController::class => Admin\EventsController::class,
        Admin\InterpretersController::class => Admin\EventsController::class,
        Admin\InterpretersWriteController::class => Admin\EventsController::class,
        Admin\DefendantsController::class => Admin\EventsController::class,
        Admin\ScheduleController::class => Admin\EventsController::class,
        Admin\CourtClosingsController::class => Admin\EventsController::class,
        Admin\EmailController::class => Admin\EventsController::class,
        Admin\NormalizationController::class => Admin\EventsController::class,
        // the topmost controller
        Main\IndexController::class => null,
        Requests\IndexController::class => null,
        Requests\WriteController::class => null,
        Admin\IndexController::class => null,
        'SDNY\Vault\Controller\VaultController' => null,
        Main\AuthController::class => null,
        // these refer to user resource ids. the User entity implements
        // Zend\Permissions\Acl\Resource\ResourceInterface
        'administrator' => null,
        'manager' => null,
        'submitter' => null,
        'staff' => null,
        // probably don't need this in production :-)
        'DoctrineORMModule\Yuml\YumlController' => null,
    ],
    // how do we configure this to use Assertions?
    // I think we don't
    'allow' => [
        //'role' => [ 'resource (controller)' => [ priv, other-priv, ...  ]
        'submitter' => [
            Requests\IndexController::class => ['index','list','view','create','update','search','cancel','help'],
            Requests\WriteController::class => ['create','update','cancel'],
            // not sure what we were thinking when this was allowed...
            //Admin\EventsController::class => ['index', 'view', 'search'],
            Main\AuthController::class => ['logout'],
        ],
        'manager' => [
            Admin\IndexController::class => null,
            Admin\LanguagesController::class => null,
            Admin\EventsController::class => null,
            // ??
            'SDNY\Vault\Controller\VaultController' => null,
            Main\AuthController::class => ['logout'],
            'submitter' => null,
        ],
        'staff' => [
            Admin\IndexController::class => ['index'],
            Main\AuthController::class => ['logout'],
        ],
        'administrator' => null,
        'anonymous' => [
            Main\AuthController::class => 'login',
        ]
    ],
    'deny' => [
        'administrator' => [
            Requests\IndexController::class => null,
            //['add','edit','update','delete','cancel','index'],
        ],

        'anonymous' => [
            Main\AuthController::class => 'logout'
        ],
    ]
];
