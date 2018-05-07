<?php
return [
    /*
    'service_manager' => [
        'factories' => [
             'doctrine.connection.orm_default' => function () {
                 $config = new \Doctrine\DBAL\Configuration();
                 $params = [
                     'user' => 'travis',
                     'password' => '',
                     'dbname' => 'test_office',
                     'driver' => 'pdo_mysql',
                     'host' => 'localhost',
                     'port' => '3306',
                 ];
                 return \Doctrine\DBAL\DriverManager::getConnection($params, $config);
             }
        ]
    ],
    */
    'doctrine' => [
        'connection' => [
            // default connection name
            'orm_default' => [
                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                'params' => [
                    'user' => 'travis',
                    'password' => '',
                    'dbname' => 'test_office',
                    'driver' => 'pdo_mysql',
                    'host' => 'localhost',
                    'port' => '3306',
                ],
            ],
        ],
    ],
];
