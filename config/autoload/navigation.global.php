<?php
/** config/autoload/navigation.global.php
 *
 * This is the config for the main admin navigation.
 *
 * Other modules under the Admin namespace have their own configurations,
 * which are merged with this.
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
                        'label' => 'schedule',
                        'route' => 'events/display',
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
                                    [
                                        'label' => 'search',
                                        'route' => 'interpreters/find_by_language',
                                    ],
                                    [
                                        'label' => 'search',
                                        'route' => 'interpreters/find_by_name',
                                    ],
                                ],
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
                                    [
                                        'label' => 'view details',
                                        'route' => 'judges/view'
                                    ],
                                ]
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
                                    [
                                        'label' => 'view details',
                                        'route' => 'users/view'
                                    ],
                                ],
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
                        'label' => 'search',
                        'route' => 'search',
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
            // the string key is necessary for other module configs to merge
            'admin'=>[
                'label' => 'admin',
                'route' => 'admin',
                'title' => 'main admin page',
                'order' => -100,
                'resource' => Admin\IndexController::class,
                'pages' => [
                    [
                        'order' => 1,
                        'label' => 'overview',
                        'route' => 'admin',
                        'title' => 'main menu page for all functions',
                        'route' => 'admin',
                        'divider' => true,
                    ],
                    [
                        'order' => 20,
                        'label' => 'people',
                        'title' => 'search across categories of people',
                        'route' => 'people',
                        'route_matches'=>['people/edit','people/add'],
                        'resource' => Admin\PeopleController::class,
                    ],
                    [
                        'order' => 35,
                        'label' => 'judges',
                        'title' => 'roster of judges',
                        'route_matches'=>['judges/edit','judges/add'],
                        'route' => 'judges',
                    ],
                    [
                        'order' => 25,
                        'label' => 'users',
                        'title' => 'user account administration',
                        'route' => 'users',
                        'route_matches'=>['users/edit','users/add'],

                    ],
                    [
                        'order' => 30,
                        'label' => 'defendant names',
                        'title' => 'names of consumers of interpreting services',
                        'route' => 'admin-defendants',
                        'route_matches'=>[
                             'admin-defendants',
                        ],
                        'resource' => Admin\DefendantsController::class,
                    ],
                    [
                        'order' => 60,
                        'title' => 'languages used in your court',
                        'label' => 'languages',
                        'route' => 'languages',
                        'route_matches'=>['languages/edit','languages/add'],
                    ],
                    [
                        'order' => 80,
                        'resource' => Admin\EventTypesController::class,
                        'label' => 'event-types',
                        'title' => 'types of interpreted events',
                        'route_matches'=>['event-types/edit','event-types/add',],
                        'route' => 'event-types',

                    ],
                    [
                        'order' => 70,
                        'title' => 'locations where interpreting services are provided',
                        'label' => 'locations',
                        'route' => 'locations',
                        'route_matches'=>['locations/edit','locations/add'],
                    ],

                    [
                        'resource' => Admin\CourtClosingsController::class,
                        'order' => 150,
                        'title' => 'holidays and ad-hoc closings of your court',
                        'label' => 'court closings',
                        'route' => 'court-closings',
                        'route_matches'=>[
                              'court-closings',
                        ],
                    ],
                ],
            ],
            [
                'order' => 0,
                'label' => 'scheduling',
                'title' => 'view the interpreters\' schedule',
                'route' => 'events',
                'route_matches'=>[
                    'events',
                    'events/display',
                    'events/edit',
                ],
                'resource' => Admin\EventsController::class,

            ],
            [
                'order' => 20,
                'label' => 'search',
                'route' => 'search',
                'resource' => Admin\SearchController::class,
                //'css_class' => 'd-none d-xl-inline',
            ],
            [
                'order' => 15,
                'label' => 'interpreters',
                'route' => 'interpreters',
                'route_matches'=>[
                    'interpreters/find_by_language',
                    'interpreters/find_by_name',
                ],
                'title' => 'manage the roster of interpreters',
                'resource' => Admin\InterpretersController::class,
                //'order' => 6000,
            ],
            'tools'=>
            [
                'label' => 'tools',
                'title' => 'yadda',
                'order' => 200,
                'uri' => '/',
                //'resource' => Admin\EventsController::class,
                'pages' => [
                    [
                        'label' => 'search',
                        'route' => 'search',
                        'order' => 1,
                        //'css_class' => 'd-none d-sm-block',
                    ],
                    [
                        'label' => 'reports',
                        'uri' => '#',
                        'order' => 2,
                        //'order' => 20,
                    ],
                    [
                        'order' => 3,
                        'label' => 'email',
                        'route' => 'email/templates',
                        'resource' => Admin\EmailController::class,
                    ],
                    [
                        'order' => 4,
                        'label' => 'support',
                        'uri' => '#',
                    ],
                ]
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            'navigation' => Laminas\Navigation\Service\DefaultNavigationFactory::class,
        ],
    ],
];
