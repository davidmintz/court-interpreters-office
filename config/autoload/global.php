<?php
/**
 * Global Configuration Override.
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */
use Laminas\Session\Storage\SessionArrayStorage;
use Laminas\Session\Validator\RemoteAddr;
use Laminas\Session\Validator\HttpUserAgent;

return
[
    'app_version' => file_get_contents(__DIR__.'/../VERSION.txt'),
    // courtesy of:
    //https://github.com/olegkrivtsov/using-zf3-book-samples/blob/master/userdemo/config/autoload/global.php
    'session_config' => [
        // maybe change these values
        'cookie_lifetime'     => 60 * 60 * 12, // Session cookie will expire in 12 hours.
        'gc_maxlifetime'      => 60 * 60 * 24 * 30, // How long to store session data on server (for 30 days).
        'save_path'           => 'data/session',
        'remember_me_seconds' => 60 * 3600 * 14 // two weeks
    ],
    // Session manager configuration.
    'session_manager' => [
        // Session validators for security.
        'validators' => [
            RemoteAddr::class,
            HttpUserAgent::class,
        ]
    ],
    // Session storage configuration.
    'session_storage' => [
        'type' => SessionArrayStorage::class
    ],
    'session_containers' => ['Authentication'],
];
