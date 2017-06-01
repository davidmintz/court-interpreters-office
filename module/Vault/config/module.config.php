<?php
namespace SDNY\Vault;
use Zend\Router\Http\Literal;

//use Zend\Router\Http\Segment;

return [
    
// override this with your local configuration
    
    'vault' => [    
        // do not change this adapter
        'adapter'       => 'Zend\Http\Client\Adapter\Curl',
        // do override these with a local configuration
        'vault_address' => 'https://vault.sdnyinterpreters.org:8200', 
        'sslcafile'     => '/usr/share/ca-certificates/ca-chain.cert.pem',
        // these settings have to match the configuration set in Vault
        'ssl_key' => '/opt/ssl/vault/int807a.nysd.key.pem',
        'ssl_cert' => '/opt/ssl/vault/usr.int807a.cert.pem',            
       
    ],    
    'service_manager' => [
        'factories' => [
            Service\Vault::class => Service\Factory\VaultServiceFactory::class,           
        ]
    ],
    'router' => [
        'routes' => [

            'vault-decrypt' => [
             'type' => Literal::class,
                'options' => [
                    'route' => '/vault/decrypt', //[/]
                    'defaults' => [
                        'module' => 'InterpretersOffice\Admin',
                        'controller' => \InterpretersOffice\Admin\Controller\VaultController::class,
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