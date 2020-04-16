<?php /** module/Admin/config/routes.php */

namespace InterpretersOffice\Admin;
use Laminas\Router\Http\Segment;
use Laminas\Router\Http\Literal;
use InterpretersOffice\Admin\Controller\CourtClosingsController;
use InterpretersOffice\Requests\Controller\Admin\IndexController as RequestsConfigController;
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
                'docket-annotations' => [
                    'type' => Segment::class,
                    'may_terminate' => true,
                    'options' => [
                        'route' => '/docket-annotations[/:docket]',
                        'defaults' => [
                            'module' => __NAMESPACE__,
                            'controller' => Controller\DocketAnnotationsController::class,
                            'action' => 'index',
                        ],
                        'constraints'=>[ 'docket' => '\d{4}-([A-Z]|[a-z]){2,4}-\d{3,5}'],
                    ],
                    'child_routes' => [
                        'add' => [
                            'type' => Segment::class,
                            'may_terminate' => true,
                            'options' => [
                                'route' => '/add',
                                'defaults' => [
                                    'action' => 'add',
                                ],
                            ],
                        ],
                        'edit' => [
                            'type' => Segment::class,
                            'may_terminate' => true,
                            'options' => [
                                'route' => '/edit/:id',
                                'defaults' => [
                                    'action' => 'edit',
                                ],
                                'constraints' => [
                                    // 'action' => 'edit|delete',
                                    'id' => '[1-9]\d*',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'docket-notes-api' => [
            'type' => Segment::class,
            'may_terminate' => false,
            'options' => [
                'route' => '/admin/docket-notes/api',
                'defaults' => [
                    'module' => __NAMESPACE__,
                    'controller' => Controller\RestfulDocketAnnotationsController::class,
                ],
            ],
            'child_routes' => [
                // 'test' => [
                //     'type' => Segment::class,
                //     'options' => [
                //         'route' => '/test',
                //         'defaults' => [
                //             'action' => 'test',
                //         ],
                //     ],
                // ],
                'put' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/update/:id',
                        'defaults' => [
                            'constraints' => [
                                'id' => '[1-9]\d*',
                            ],
                        ],
                    ],
                ],
                'post' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/create',
                    ]
                ],
                'delete' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/delete/:id',
                        'defaults' => [
                            'constraints' => [
                                'id' => '[1-9]\d*',
                            ],
                        ],
                    ],
                ],
                'count-events'=>[
                    'type' => Segment::class,
                    'options'=>[
                        'route' => '/count-events',
                        'defaults'=>[
                            'action' => 'count-events',
                        ],
                    ],
                ],
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
                'view' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '[/:class]/:id',
                        'defaults' => [
                            'action' => 'view',
                        ],
                        'constraints' => [
                            'action' => 'view',
                            'id' => '[1-9]\d*',
                            'class' => 'user|judge'
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
                'view' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/:id',
                        'defaults' => [
                            'action' => 'view',

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
                'route' => '/admin/interpreters',

                'defaults' => [
                    'module' => __NAMESPACE__,
                    'controller' => Controller\InterpretersController::class,
                    'action' => 'index',
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
                 'autocomplete_banned_list' => [
                     'type' => Segment::class,
                     'options' => [
                         'route' => '/autocomplete/banned',
                         'defaults' => [
                             'controller' => Controller\InterpretersWriteController::class,
                             'action' => 'autocomplete-banned-list',
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
                'send-list' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/send-list',
                        'defaults' => [
                            'controller' => Controller\EmailController::class,
                            'action' => 'send-interpreter-list'
                        ],
                        'constraints' => [
                        
                        ],
                    ],
                ],
                'availability_list' => [
                    'type' => Segment::class,
                    'may_terminate' => false,
                    'options' => [
                        'route' => '/availability',
                        
                    ],
                    'child_routes' => [
                        'update' => [
                            'type' => Segment::class,
                            'options'=>[
                                'route'=>'/list/update',
                                'defaults' => [
                                    'controller' => Controller\InterpretersWriteController::class,
                                    'action'=> 'update-availability-list',
                                ],
                            ],
                        ],
                        'list'=> [
                            'type' => Segment::class,
                            'options'=>[
                                'route'=>'/list[/language/:language]',
                                'defaults' => [
                                    'action'=> 'availability-list',
                                ],
                            ],
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
                'view' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '[/:entity]/:id',
                        'defaults' => [
                            'action' => 'view',
                        ],
                        'constraints' => [
                            'id' => '[1-9]\d*',
                            'entity'=>'person',
                        ],
                    ],
                ],
                'edit' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/:action[/:entity]/:id',
                        'defaults' => [
                            'action' => 'edit',
                        ],
                        'constraints' => [
                            'action' => 'edit|delete',
                            'id' => '[1-9]\d*',
                            'entity'=>'person',
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
                'autocomplete' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/autocomplete',
                        'defaults' => [
                            'action' => 'autocomplete',


                        ],
                    ],
                ],
                'search' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/search',
                        'defaults' => [
                            'action' => 'search',
                        ],

                    ],
                ],
            ],
        ],
        'court-closings' => [
            'type' => Segment::class,
            'may_terminate' => true,
            'options' => [
                'route' => '/admin/court-closings',
                'defaults' => [
                    'module' => __NAMESPACE__,
                    'controller' => Controller\CourtClosingsController::class,
                    'action' => 'index',
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
            'type' => Segment::class,
            'options' => [
                'route' => '/admin/email',
                'defaults' => [
                    'module' => __NAMESPACE__,
                    'controller' => Controller\EmailController::class,
                    'action' => 'index',
                ],
            ],
            'may_terminate' => true,
            'child_routes' => [
                'batch' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/batch',
                        'defaults' => [
                            'action' => 'form',
                        ],
                    ],
                ],
                /* experimental */
                'batch-email' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/send',
                        'defaults' => [
                            'action' => 'batch-email',
                        ],
                    ],
                ],
                /* experimental */
                'batch-progress' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/progress',
                        'defaults' => [
                            'action' => 'progress',
                        ],
                    ],
                ],
                'preview' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/preview',
                        'defaults' => [
                            'action' => 'preview',
                        ],
                    ],
                ],
                'event' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/event',
                        'defaults' => [
                            'action' => 'email-event',
                        ],
                    ],
                ],
                'templates' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/templates',
                        'defaults' => [
                            'action' => 'templates',
                        ],
                    ],
                ],
            ],
        ],
        'search' => [
            'type' => Segment::class,
            'may_terminate' => true,
            'options' => [
                'route' => '/admin/search',
                'defaults' => [
                    'module' => __NAMESPACE__,
                    'controller' => Controller\SearchController::class,
                    'action' => 'search',
                ],
            ],
            'child_routes' => [
                'docket' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/docket/:docket',
                        'defaults' => ['action'=>'docket-search'],
                        'constraints'=>[ 'docket' => '\d{4}-([A-Z]|[a-z]){2,4}-\d{3,5}'],
                    ],
                ],
                'clear' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/clear',
                        'defaults' => ['action'=>'clear'],
                    ],
                ],
            ],
        ],
        'normalization' => [
            'type' => Segment::class,
            'may_terminate' => true,
            'options' => [
                'route' => '/admin/normalize',
                'defaults' => [
                    'module' => __NAMESPACE__,
                    'controller' => Controller\NormalizationController::class,
                    'action' => 'index',
                ],
            ],
        ],
        'configuration' => [
            'type' => Segment::class,
            'may_terminate' => true,
            'options' => [
                'route' => '/admin/configuration',
                'defaults' => [
                    'module' => __NAMESPACE__,
                    'controller' => Controller\ConfigController::class,
                    'action' => 'index',
                ],
            ],
            'child_routes' => [
                'requests' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/requests',
                        'defaults' => [
                            'controller' => RequestsConfigController::class,
                            'action' => 'config',
                        ],
                    ],
                ],
                'forms' => [
                    'type' => Segment::class,
                    'may_terminate' => true,
                    'options' => [
                        'route' => '/forms',
                        'defaults' => [
                            'controller' => Controller\ConfigController::class,
                            'action' => 'forms',
                        ],
                    ],
                    'child_routes' => [
                        'update' => [
                            'type' => Segment::class,
                            'options' => [
                                'route' => '/update',
                                'defaults' => [
                                    'action' => 'post',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ],
        'reports' => [
            'type' => Segment::class,
            'may_terminate' => true,
            'options' => [
                'route' => '/admin/reports',
                'defaults' => [
                    'module' => __NAMESPACE__,
                    'controller' => Controller\ReportsController::class,
                    'action' => 'index',
                ],
            ],
        ],
    ],
 ];
