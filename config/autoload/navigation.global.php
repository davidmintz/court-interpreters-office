<?php
/** config/autoload/navigation.global.php
 *
 * still a work in progress
 */
use InterpretersOffice\Controller as Main;

use InterpretersOffice\Admin\Controller as Admin;

return [

    'navigation' => [
        // breadcrumbs navigation helper
        'admin_breadcrumbs' =>
        [
            [
                'label' => 'admin',
                'route' => 'admin',
                'pages' => [
                    [
                        'label' => 'judges',
                        'route' => 'judges',
                        'pages' => [
                            [
                                'label' => 'add',
                                'route' => 'judges/add'
                            ],
                            [
                                'label' => 'edit',
                                'route' => 'judges/edit'
                            ],
                        ]
                    ],
                    [
                        'label' => 'schedule',
                        'route' => 'events',
                        'pages' => [
                            [
                                'label' => 'add',
                                'route' => 'events/add'
                            ],
                            [
                                'label' => 'edit',
                                'route' => 'events/edit'
                            ],
                        ],
                    ],
                    [
                        'label' => 'interpreters',
                        'route' => 'interpreters',
                        //'resource' => Admin\InterpretersController::class,
                        'pages' => [
                            [
                                'label' => 'add',
                                'route' => 'interpreters/add'
                            ],
                            [
                                'label' => 'edit',
                                'route' => 'interpreters/edit',
                            ],
                            [
                                'label' => 'view details',
                                'route' => 'interpreters/find_by_id',
                            ],
                        ],
                    ],
                    [
                        'label' => 'interpreters',
                        'route' => 'interpreters/find_by_language',

                    ],
                    [
                        'label' => 'interpreters',
                        'route' => 'interpreters/find_by_name',
                    ],
                    [
                        'label' => 'languages',
                        'route' => 'languages',
                        'pages' => [
                            [
                                'label' => 'add',
                                'route' => 'languages/add'
                            ],
                            [
                                'label' => 'edit',
                                'route' => 'languages/edit',
                            ]
                        ]
                    ],
                    [
                        'label' => 'people',
                        'route' => 'people',
                        'pages' => [
                            [
                            'label' => 'add',
                            'route' => 'people/add'
                            ],
                            [
                            'label' => 'edit',
                            'route' => 'people/edit',
                            ],
                            [
                            'label' => 'view details',
                            'route' => 'people/view',
                            ],
                        ],
                    ],
                    [
                        'label' => 'event-types',
                        'route' => 'event-types',
                        'pages' => [
                            [
                                'label' => 'add',
                                'route' => 'event-types/add'
                            ],
                            [
                                'label' => 'edit',
                                'route' => 'event-types/edit'
                            ],
                        ],
                    ],
                    [
                        'label' => 'users',
                        'route' => 'users',
                        'pages' => [
                            [
                                'label' => 'add',
                                'route' => 'users/add'
                            ],
                            [
                                'label' => 'edit',
                                'route' => 'users/edit'
                            ],
                        ]
                    ],
                    [
                        'label' => 'locations',
                        'route' => 'locations',
                        'pages' => [
                            [
                                'label' => 'add',
                                'route' => 'locations/add'
                            ],
                            [
                                'label' => 'edit',
                                'route' => 'locations/edit'
                            ],
                        ]
                    ],
                    [
                        'label' => 'defendants',
                        'route' => 'admin-defendants',
                        'pages' => [
                            [
                                'label' => 'add',
                                'route' => 'admin-defendants/add'
                            ],
                            [
                                'label' => 'edit',
                                'route' => 'admin-defendants/edit'
                            ],
                        ]
                    ],
                    [
                        'label' => 'court closings',
                        'route' => 'court-closings',
                        'pages' => [
                            [
                                'label' => 'add',
                                'route' => 'court-closings/add'
                            ],
                            [
                                'label' => 'edit',
                                'route' => 'court-closings/edit'
                            ],
                        ]
                    ]
                ],
            ],
        ],
        // main navigation menu
        'default' => [
            [
                'label' => 'admin',
                'route' => 'admin',
                'title' => 'main admin page',
                'resource' => Admin\IndexController::class,
            ],
            [
                'label' => 'schedule',
                'title' => 'view the interpreters\' schedule',
                'route' => 'events',
                'resource' => Admin\EventsController::class,

            ],
            [
                'label' => 'add event',
                'route' => 'events/add',
                'title' => 'create a new event on the schedule',
                'resource' => Admin\EventsController::class,
                'privilege' => 'add',
            ],
            [
                'label' => 'interpreters',
                'route' => 'interpreters',
                'title' => 'manage the roster of interpreters',
                'resource' => Admin\InterpretersController::class,
            ],
            [
                'label' => 'other data',
                'route' => 'admin',
                'title' => 'manage other data entities',
                'resource' => Admin\IndexController::class,
                'order' => 600,
                'pages' => [

                    [
                        'label' => 'languages',
                        'route' => 'languages',
                    ],
                    [
                        'label' => 'locations',
                        'route' => 'locations',
                    ],
                    [
                        'label' => 'judges',
                        'route' => 'judges',
                        'pages' => [
                            [
                                'label' => 'add',
                                'route' => 'judges/add'
                            ],
                            [
                                'label' => 'edit',
                                'route' => 'judges/edit'
                            ],
                        ]
                    ],
                    [
                        'label' => 'users',
                        'route' => 'users',
                    ],
                    [
                        'label' => 'people',
                        'route' => 'people',
                        'resource' => Admin\PeopleController::class,
                    ],
                    [
                        'resource' => Admin\EventTypesController::class,
                        'label' => 'event-types',
                        'route' => 'event-types',
                    ],
                    [
                        'label' => 'defendants',
                        'route' => 'admin-defendants',
                        'resource' => Admin\DefendantsController::class,
                        'expand_children' => false,
                        'pages' => [
                            [
                                'label' => 'add',
                                'route' => 'admin-defendants/add'
                            ],
                            [
                                'label' => 'edit',
                                'route' => 'admin-defendants/edit'
                            ],
                        ]
                    ],
                    [
                        'resource' => Admin\CourtClosingsController::class,
                        'label' => 'court closings',
                        'route' => 'court-closings',
                    ],
                ],
            ],

            [
                'label' => 'tools',
                'title' => 'yadda',
                //'route' => 'events',
                'order' => 700,
                'uri' => '/',
                //'resource' => Admin\EventsController::class,
                'pages' => [
                    [
                        'label' => 'search',
                        'uri' => '#'
                    ],
                    [
                        'label' => 'reports',
                        'uri' => '#'
                    ],
                    [
                        'label' => 'email',
                        'route' => 'email/templates',
                        //'uri' => '/admin/email/templates',
                        'resource' => Admin\EmailController::class,
                    ],
                    [
                        'label' => 'help',
                        'uri' => '#'
                    ],

                ]

            ],
            // [
            //     'label' => 'help',
            //     'title' => 'get help',
            //     //'route' => 'events',
            //     'order' => 700,
            //     'uri' => '/',
            //     //'resource' => Admin\EventsController::class,
            //
            // ],

        ],
    ],
    'service_manager' => [
        'factories' => [
            'navigation' => Zend\Navigation\Service\DefaultNavigationFactory::class,
        ],
    ],
];
