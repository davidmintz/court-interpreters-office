<?php
namespace InterpretersOffice\Admin\Notes;

return [
    'doctrine' => [
        'driver' => [
            'application_annotation_driver' => [
                'paths' => [
                    __DIR__.'/../src/Entity',
                ],
            ],
            'orm_default' => [
                'drivers' => [
                    __NAMESPACE__.'\Entity' => 'application_annotation_driver',
                ],
            ],
        ],
    ],
    'navigation' => [
        'default' => [
             'tools' => [
                 //'order' => 40,
                 'pages' => [
                     [
                         'label' => 'notes',
                         'uri' => '#',
                         //'order' => -1000,
                         'pages' => [
                             'foo' => [
                                 'label' => 'foo',
                                 'uri' => '#',
                             ],
                             'bar' => [
                                 'label' => 'bar',
                                 'uri' => '#',
                             ],
                         ]
                     ],
                 ],
             ]
        ],
    ],
];
