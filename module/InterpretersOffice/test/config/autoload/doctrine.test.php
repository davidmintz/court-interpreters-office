<?php
// we need to fix this.

// does NOT work when you are in module/InterpretersOffice/test !
// $path = getcwd() . '/module/InterpretersOffice/test/data/office.sqlite';

//DOES work from module/Application/test
$path = realpath( __DIR__.'/../../data/office.sqlite');
use InterpretersOffice\Entity;

return [

    'service_manager' => [
        'factories' => [
             'doctrine.connection.orm_default' => function () use ($path) {
                 $config = new \Doctrine\DBAL\Configuration();
                 $params = [
                      'driver' => 'pdo_sqlite',
                      'path' => $path,
                       // we can do this if we need it:
                      'driverOptions' => [
                        // 'userDefinedFunctions' => [
                        //     'md5' => ['callback' => function ($string) {
                        //         return md5($string);
                        //     }, 'numArgs' => 1],
                        // ],
                      ],
                 ];

                 return \Doctrine\DBAL\DriverManager::getConnection($params, $config);
             },
        ],
    ],
    // it appears that we don't really need this, but maybe we will:
    ///*
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'driverClass' => 'Doctrine\DBAL\Driver\PDOSqlite\Driver',
                'params' => [
                    'path' => $path,
                    'driver' => 'pdo_sqlite',
                    'user' => 'travis',
                    'password' => '',
                    'dbname' => 'test_office',
                    //'driver' => 'pdo_mysql',
                    //'host' => 'localhost',
                    //'port' => '3306',

                ],
            ],
             'configuration' => [
                'orm_default' => [
                   'query_cache' => 'array',
                   'result_cache' => 'array',
                   'metadata_cache' => 'array',
                   'hydration_cache' => 'array',
                ],
             ],
        ],

        'eventmanager' => [
            'orm_default' => [
                'subscribers' => [
                    Entity\Listener\UpdateListener::class,
                ],
            ],
        ],
        // this is used to inject dependencies into repository classes
        // and requires us to use a FactoryFactory [sic]
        //'configuration' => [
        //    'orm_default' =>   ['repository_factory'=>'InterpretersOffice\Service\Factory\RepositoryFactory'],
        //],
    ],
    // */
];
