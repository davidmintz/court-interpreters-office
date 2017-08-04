<?php /** config/autoload/navigation.global.php */
return [

    'navigation' => [
        'default' => [
              [
                  'label'=>'schedule',
                  'route' => 'events',
              ],
              [
                  'label'=>'interpreters',
                  'route' => 'interpreters',
              ],
              [                
                'label' => 'admin',
                'route' => 'admin',
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
                ]
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            'navigation' => Zend\Navigation\Service\DefaultNavigationFactory::class,
        ],
    ],
];
