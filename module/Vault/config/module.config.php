<?php
/** module/Vault/config/module.config.php */

namespace SDNY\Vault;

use Laminas\Router\Http\Literal;

//use Laminas\Router\Http\Segment;

return [

// override this with your local configuration

    'vault' => [
        // set to false in a local configuration to disable
        'enabled' => true,
        // do not change this adapter
        'adapter'       => 'Laminas\Http\Client\Adapter\Curl',

        // ==== override the following in your local configuration =====//
        'vault_address' => 'https://office.localhost:8200',
        //'sslcafile'     => '/usr/share/ca-certificates/ca-chain.cert.pem',

        // path for application to get token with access to secret
        'path_to_access_token' => '/auth/token/create/read-secret',
        // path to secret within Vault
        'path_to_secret' => '/path/to/your/secret', // including leading slash

        // these settings must match the TLS authentication configured in Vault
        'ssl_key' => '/path/to/your/key.pem',
        'ssl_cert' => '/path/to/your/cert.pem',
        // ===================================== //


    ],
    'service_manager' => [
        'factories' => [
            Service\Vault::class => Service\Factory\VaultServiceFactory::class,
        ]
    ],
    'controllers' => [
        'factories' => [
            Controller\VaultController::class => Controller\Factory\VaultControllerFactory::class,
        ]
    ],
    'router' => [
        'routes' => [
            'vault-decrypt' => [
             'type' => Literal::class,
                'options' => [
                    'route' => '/vault/decrypt', //[/]
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\VaultController::class,
                        'action' => 'decrypt',
                    ],
                ],
            ],
        ],
    ],
    'acl' => [

        // maybe put our ACL stuff here?
    ]
];
