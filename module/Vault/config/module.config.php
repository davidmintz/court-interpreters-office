<?php
namespace SDNY\Vault;

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
            Service\Vault::class => Service\Factory\VaultServiceFactory::class
        ]
    ]
];