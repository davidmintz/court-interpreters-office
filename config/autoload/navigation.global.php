<?php
/** config/autoload/navigation.global.php 
 *
 * still a work in progress
 */
return [

    'navigation' => [
        'default' => [
            [
                'label' => 'schedule',
                'route' => 'events',
                'resource' => 'events',
                'expand_children' => false,
                'pages' =>
                [
                    [
                        'label' => 'add',
                        'route' => 'events/add'
                    ],
                ],
            ],
            // yes, two of these: the above to make the breadcrumbs helper work,
            // the next for the main nav
            [
                'label' => 'add event',
                'route' => 'events/add',
                'resource' => 'interpreters',
                'privilege' => 'add',
            ],
            [
                'label' => 'interpreters',
                'route' => 'interpreters',
                'resource' => 'interpreters',
                'expand_children' => false,
                'pages' => [
                    [
                        'label' => 'add',
                        'route' => 'interpreters/add'
                    ],
                    [
                        'label' => 'edit',
                        'route' => 'interpreters/edit',
                    ]
                ]
            ],
            [
                'label' => 'admin',
                'route' => 'admin',
                'resource' => 'admin-index',
                //'privilege' => 'index','action' => 'index',
                'pages' => [
                    [
                        'label' => 'main',
                        'route' => 'admin',
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
                        ]
                    ],
                ],
            ],
            [
                'label' => 'log out',
                'route' => 'logout',
                'resource' => 'auth',
                'privilege' => 'logout',
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            'navigation' => Zend\Navigation\Service\DefaultNavigationFactory::class,
        ],
    ],
];
