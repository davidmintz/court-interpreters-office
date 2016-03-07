<?php

// Composer autoloading
if (file_exists('vendor/autoload.php')) {
    $loader = include 'vendor/autoload.php';
}

if (! class_exists('Zend\Loader\AutoloaderFactory')) {
    throw new RuntimeException('Unable to load Zend Framework. Make sure it is installed in your `vendor` folder.');
}

