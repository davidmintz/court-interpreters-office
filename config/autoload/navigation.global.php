<?php

return [

    'navigation' => [
        'default' => [
            [
                'label' => 'admin',
                'route' => 'admin',
                'pages' => [
                    [
                    'label' => 'languages',
                    'route' => 'languages',
                    /*
                    'pages' => [
                        [
                            'label' => 'add new',
                            'route' => 'languages/add'
                        ]
                    ]
                     
                     */
                    ],
                    [
                    'label' => 'locations',
                    'route' => 'locations',
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
