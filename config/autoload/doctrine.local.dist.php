<?php

return [
	'doctrine' => array(
	    'connection' => array(
            // default connection name
            'orm_default' => array(
                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                'params' => array(
                    'host'     => 'localhost',
                    'port'     => '3306',
                    'driverOptions' => array(
                        \PDO::MYSQL_ATTR_INIT_COMMAND  => 'SET NAMES utf8'
                    ),
                )
            )
        )
	),
];