<?php
namespace SDNY\Vault;
use Zend\Router\Http\Literal;

//use Zend\Router\Http\Segment;

return [
    
// override this with your local configuration
    
    'vault' => [    
       
        // do override these with a local configuration
        'vault_address' => 'https://vault.sdnyinterpreters.org:8200', 
        'sslcafile'     => '/usr/share/ca-certificates/ca-chain.cert.pem',
        // these settings must match the configuration set in Vault
        'ssl_key' => '/opt/ssl/vault/int807a.nysd.key.pem',
        'ssl_cert' => '/opt/ssl/vault/usr.int807a.cert.pem',    
        'path_to_secret' => '/path/to/your/secret', // including leading slash
         'path_to_secret' => '/secret/sdny/encryption',
        // do not change this adapter
        'adapter'       => 'Zend\Http\Client\Adapter\Curl',
       
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
                        'module' =>__NAMESPACE__,
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