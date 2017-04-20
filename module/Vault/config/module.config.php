<?php
namespace SDNY\Vault;

return [
    
// override this with your local configuration
    
    'vault' => [    
        'vault_address' => 'https://vault.sdnyinterpreters.org:8200', 
        'sslcafile'     => '/usr/share/ca-certificates/ca-chain.cert.pem',        
    ],
    'service_manager' => [
        'factories' => [
            Service\Vault::class => Service\Factory\VaultServiceFactory::class
        ]
    ]
];