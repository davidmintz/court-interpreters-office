<?php

require __DIR__.'/../../vendor/autoload.php';
use Laminas\Http\Client;
use Laminas\Http\Response;
$config = [
    'adapter'     => Client\Adapter\Curl::class,
    'curloptions'=>[
        \CURLOPT_SSLKEY => '/opt/vault/certs/auth.vault.localhost.key.pem',
        \CURLOPT_SSLCERT => '/opt/vault/certs/auth.vault.localhost.cert.pem',
    ]
];
/*
'ssl_key' => '/opt/vault/certs/auth.vault.localhost.key.pem',
'ssl_cert' => '/opt/vault/certs/auth.vault.localhost.cert.pem',
*/
/** @var Laminas\Http\Client $client */
$client = new Client(
    null, $config
);
var_dump($config);
$client->setMethod('POST')->setUri('https://vault.localhost:8200/v1/auth/cert/login');
$client->getRequest()->getHeaders()->addHeaderLine('Accept: application/json');
/** @var Laminas\Http\Response $response */
$response = $client->send();

/*$ curl \
    --request POST \
    --cacert ca.pem \
    --cert cert.pem \
    --key key.pem \
    --data '{"name": "web"}' \
    http://127.0.0.1:8200/v1/auth/cert/login
*/
echo $response->getStatusCode(),"\n";

$data = json_decode($response->getBody());

exit("whatever\n");

/*
sslcapath 	Path to SSL certificate directory 	string 	NULL
sslcafile 	Path to Certificate Authority (CA) bundle 	string 	NULL
 */
