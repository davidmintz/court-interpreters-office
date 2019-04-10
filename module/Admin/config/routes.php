<?php /** module/Admin/config/routes.php */
namespace InterpretersOffice\Admin;

use Zend\Router\Http\Segment;
use Zend\Router\Http\Literal;
use InterpretersOffice\Admin\Controller\CourtClosingsController;

//$today = new \DateTime();

return  [
    'routes' => [
        'admin' => [
            'type' => Segment::class,
            'may_terminate' => true,
            'options' => [
                'route' => '/admin',
                'defaults' => [
                    'module' => __NAMESPACE__,
                    'controller' => Controller\IndexController::class,
                    'action' => 'index',
                ],
            ],
            'child_routes' => [

            ],
        ],
        'events_index' => [
            'type' => Literal::class,
            'may_terminate' => true,
            'options' => [
                'route'=>'/admin/events',
                'defaults' => [
                    'module' => __NAMESPACE__,
                    'controller' => Controller\EventsController::class,
                    'action' => 'index',
                ],
            ],
        ],
        'events' => [
            'type' => Segment::class,
            'may_terminate' => true,
            'options' => [
                'route' => '/admin/schedule',
                'defaults' => [
                    'module' => __NAMESPACE__,
                    'controller' => Controller\ScheduleController::class,
                    'action' => 'schedule',
                    /*
                    'year' => $today->format('Y'),
                    'month' => $today->format('m'),
                    'date' => $today->format('d'),
                     */
                ],
            ],
            'child_routes' => [

                'display' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/:year/:month[/:date]',
                        'defaults' => [
                            'controller' => Controller\ScheduleController::class,
                            'action' => 'schedule',//'year'=>null,
                            // these could use refining.
                        ],
                        'constraints' => [
                            'year' => '[12]\d\d\d',
                            'month' => '\d\d',
                            'date' => '\d\d'
                        ],
                    ]
                ],
                'view' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/view/:id',
                        'defaults' => [
                            'controller' => Controller\ScheduleController::class,
                            'action' => 'view',
                        ],
                        'constraints' => [
                            'id' => '[1-9]\d*',
                        ],
                    ],
                ],
                'add' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/add[/repeat/:id]',
                        'defaults' => [
                            'controller' => Controller\EventsController::class,
                            'action' => 'add',
                        ],
                        'constraints' => [
                            'id' => '[1-9]\d*',
                        ],
                    ],
                ],
                'edit' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/:action/:id',
                        'defaults' => [
                            'controller' => Controller\EventsController::class,
                            'action' => 'edit',
                        ],
                        'constraints' => [
                            'action' => 'edit|delete|repeat|update-interpreters',
                            'id' => '[1-9]\d*',
                        ],
                    ],
                ],

                'interpreter-template' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/interpreter-template',
                        'defaults' => [
                            'controller' => Controller\EventsController::class,
                            'action' => 'interpreter-template',
                        ],
                    ],
                ],
                'interpreter-options' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/interpreter-options',
                        'defaults' => [
                            'controller' => Controller\EventsController::class,
                            'action' => 'interpreter-options',
                        ],
                    ],
                ],
                'update-interpreters' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/update-interpreters/:id',
                        'defaults' => [
                            'controller' => Controller\EventsController::class,
                            'action' => 'update-interpreters',
                        ],
                    ],
                ],
                'get-modification-time' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/get-modification-time/:id',
                        'defaults' => [
                            'controller' => Controller\EventsController::class,
                            'action' => 'get-modification-time',
                            'id' => '[1-9]\d*',
                        ],
                    ],
                ]
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
                'type' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/type/:id',
                        'defaults' => [
                            'action' => 'index',
                        ],
                        'constraints' => [
                            'id' => '[1-9]\d*',
                        ],
                    ],
                ],

                /* @todo this will have to be moved or copied to a
                 * non-admin controller but this is convenient for now
                 */
                'courtrooms' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/courtrooms/:parent_id',
                        'defaults' => [
                            'action' => 'courtrooms',
                        ],
                        'constraints' => [
                            'parent_id' => '[1-9]\d*',
                        ],
                    ],
                ],
                'add' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/add[/type/:type_id]',
                        'defaults' => [
                            'action' => 'add',
                        ],
                        'constraints' => [
                            'type_id' => '[1-9]\d*',
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
        'event-types' => [
            'type' => Segment::class,
            'may_terminate' => true,
            'options' => [
                'route' => '/admin/event-types',
                'defaults' => [
                    'module' => __NAMESPACE__,
                    'controller' => Controller\EventTypesController::class,
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
        'people' => [
            'type' => Segment::class,
            'may_terminate' => true,
            'options' => [
                'route' => '/admin/people',
                'defaults' => [
                    'module' => __NAMESPACE__,
                    'controller' => Controller\PeopleController::class,
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
                'find' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/:action',
                        'defaults' => [
                            'action' => 'search',
                        ],
                        'constraints' => [
                            'action' => 'autocomplete|search',
                        ],
                    ],
                ],

                'people_options' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/get',
                        'defaults' => [
                            'action' => 'get',
                        ],
                    ],
                ],
            ],
        ],
        'judges' => [
            'type' => Segment::class,
            'may_terminate' => true,
            'options' => [
                'route' => '/admin/judges',
                'defaults' => [
                    'module' => __NAMESPACE__,
                    'controller' => Controller\JudgesController::class,
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
        'admin-defendants' => [
            'type' => Segment::class,
            'may_terminate' => true,
            'options' => [
                'route' => '/admin/defendants',
                'defaults' => [
                    'module' => __NAMESPACE__,
                    'controller' => Controller\DefendantsController::class,
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
                            'action' => 'edit|delete|update-existing',
                            'id' => '[1-9]\d*',
                        ],
                    ],
                ],
            ],
        ],
        'interpreters' => [
            'type' => Segment::class,
            'may_terminate' => true,
            'options' => [
                'route' => '/admin/interpreters',//[/list] was an experiment

                'defaults' => [
                    'module' => __NAMESPACE__,
                    'controller' => Controller\InterpretersController::class,
                    'action' => 'index',
                    // defaults for interpreter roster search terms
                    //'active' => 1, // by default, active only
                    //'security_clearance_expiration'=> 1, // by default, valid security clearance status
                    //'language_id' => 0,
                    // 'name' => '',

                ],

            ],
            'child_routes' => [
                'add' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/add',
                        'defaults' => [
                            'controller' => Controller\InterpretersWriteController::class,
                            'action' => 'add',
                        ],
                    ],
                ],
                'edit' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/:action/:id',
                        'defaults' => [
                            'controller' => Controller\InterpretersWriteController::class,
                            'action' => 'edit',
                        ],
                        'constraints' => [
                            'action' => 'edit|delete',
                            'id' => '[1-9]\d*',
                        ],
                    ],
                ],
                // partial validation for interpreters form
                // when they change tabs
                 'validate-partial'  => [

                    'type' => Segment::class,
                    'options' => [
                        'route' => '/validate-partial',
                        'defaults' => [
                            'controller' => Controller\InterpretersWriteController::class,
                            'action' => 'validate-partial',
                        ],
                    ],
                 ],
                // for generating markup for an interpreter-language
                'language-fieldset' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/language-fieldset',
                        'defaults' => [
                            'controller' => Controller\InterpretersWriteController::class,
                            'action' => 'language-fieldset',
                        ],
                    ],
                ],
                 'find_by_id' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/:id',
                        'defaults' => [
                            'action' => 'index',
                        ],
                        'constraints' => [
                            'id' => '[1-9]\d*',
                        ],
                    ],
                 ],
                'find_by_name' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/name/:lastname[/:firstname]',
                        'defaults' => [
                            'action' => 'index',
                            // for name-search text input
                            //'name' => '',
                            //'firstname'   => '',
                        ],
                    ],
                ],
                ///*
                'find_by_language' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/language/:language_id[/active/:active[/security/:security_clearance_expiration]]',
                        'constraints' => [
                            'language_id' => '[0-9]\d*',
                            'active' => '-?1|0',
                            // any value, as long as it's -2, -1, 0 or 1
                            'security_clearance_expiration' => '-[12]|[01]',
                        ],
                    ],
                ],
            ],
        ],

        'users' => [
            'type' => Segment::class,
            'may_terminate' => true,
            'options' => [
                'route' => '/admin/users',
                'defaults' => [
                    'module' => __NAMESPACE__,
                    'controller' => Controller\UsersController::class,
                    'action' => 'index',
                ],
            ],
            'child_routes' => [
                'add' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/add[/person/:id]',
                        'defaults' => [
                            'action' => 'add',
                        ],
                        'constraints' => [
                            'id' => '[1-9]\d*',
                        ]
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
                'role-options' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/role-options/:hat_id',
                        'defaults' => [
                            'action' => 'get-role-options-for-hat',

                        ],
                        'constraints' => [
                            'hat_id' => '[1-9]\d*',
                        ],
                    ],
                ],
                'find-person' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/find-person',
                        'defaults' => [
                            'action' => 'find-person',

                        ],
                    ],
                ],
            ],
        ],
        'court-closings' => [
            'type' => Segment::class,
            'may_terminate' => true,
            'options' => [
                'route' => '/admin/court-closings',//[/year/:year]',
                'defaults' => [
                    'module' => __NAMESPACE__,
                    'controller' => Controller\CourtClosingsController::class,
                    'action' => 'index',
                    //'constraints' => ['year' => '[20]\d\d',],
                ],
            ],
            'child_routes' => [
                'year' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/year/:year',
                        'defaults' => [
                            'action' => 'index',
                            'constraints' => [
                                'year' => '[20]\d\d',
                            ],
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
                // experimental
                'test' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/test',
                        'defaults' => [
                            'action' => 'test',
                        ],
                    ],
                ]
            ],
        ],
        'vault-test' => [
            'type' => Literal::class,
            'options' => [
                'route' => '/vault/test', //[/]
                'defaults' => [
                    'module' => __NAMESPACE__,
                    'controller' => Controller\VaultController::class,
                    'action' => 'test',
                ],
            ],
        ],
        'vault-authenticate' => [
         'type' => Literal::class,
            'options' => [
                'route' => '/vault/authenticate-app', //[/]
                'defaults' => [
                    'module' => __NAMESPACE__,
                    'controller' => Controller\VaultController::class,
                    'action' => 'authenticate-app',
                ],
            ],
        ],

        'email' => [
            'type' => Literal::class,
               'options' => [
                   'route' => '/admin/email/event', //[/]
                   'defaults' => [
                       'module' => __NAMESPACE__,
                       'controller' => Controller\EmailController::class,
                       'action' => 'email-event',
                   ],
               ],
        ],
    ],
 ];
