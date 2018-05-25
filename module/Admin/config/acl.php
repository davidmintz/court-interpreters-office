<?php
/** module/Admin/config/acl.php
 *
 *  ACL configuration based on LearnZF2.
 */
use InterpretersOffice\Admin\Controller as Admin;
use InterpretersOffice\Controller as Main;
use InterpretersOffice\Requests\Controller as Requests;

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
        // the topmost controller
        Main\IndexController::class => null,
        Requests\IndexController::class => null,
        Admin\AdminIndexController::class => null,
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
            Requests\IndexController::class => ['create', 'view', 'index'],
            Admin\EventsController::class => ['index', 'view', 'search'],
            Main\AuthController::class => ['logout'],
        ],
        'manager' => [
            Admin\AdminIndexController::class => null,
            Admin\LanguagesController::class => null,
            Admin\EventsController::class => null,
            // ??
            'SDNY\Vault\Controller\VaultController' => null,
            Main\AuthController::class => ['logout'],
            'submitter' => null,
        ],
        'staff' => [
            Admin\AdminIndexController::class => ['index'],
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
