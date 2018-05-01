<?php
return [
    
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
