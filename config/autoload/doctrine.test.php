<?php
/**
 * config/autoload/doctrine.test.php
 * used with travis-ci in 'before_script' directive.
 */
$path = getcwd().'/module/Application/test/data/office.sqlite';

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
                        'userDefinedFunctions' => [
                            'md5' => ['callback' => function ($string) {
                                return md5($string);
                            }, 'numArgs' => 1],
                        ],
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
                'params' => ['path' => $path],
            ],
        ],
        // this is used to inject dependencies into repository classes
        // and requires us to use a FactoryFactory [sic]
        //'configuration' => [
        //    'orm_default' =>   ['repository_factory'=>'Application\Service\Factory\RepositoryFactory'],
        //],
    ],
    // */
];
