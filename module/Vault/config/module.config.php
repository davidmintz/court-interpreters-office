<?php
namespace SDNY\Vault;

return [
    
// override this with your local configuration
    
    'vault' => [    
        'vault_address' => 'https://vault.sdnyinterpreters.org:8200', 
        'sslcafile'     => '/usr/share/ca-certificates/ca-chain.cert.pem',
        // do not change this adapter
        'adapter'       => 'Zend\Http\Client\Adapter\Curl',
        // do override these
        'curl_options' => [            
            \CURLOPT_SSLKEY => '/opt/ssl/vault/int807a.nysd.key.pem',
            \CURLOPT_SSLCERT => '/opt/ssl/vault/usr.int807a.cert.pem',            
        ],
    ],
    'service_manager' => [
        'factories' => [
            Service\Vault::class => Service\Factory\VaultServiceFactory::class
        ]
    ]
];