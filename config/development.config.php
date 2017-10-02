<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 *
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
$path = __DIR__.'/zend-developer-tools.toggle.php';
if (file_exists($path)) {
    $zf_tools = trim(file_get_contents(__DIR__.'/zend-developer-tools.toggle.php'));
    $modules = (boolean)$zf_tools ? [ 'ZendDeveloperTools', ] : [];
} else {
    $modules = [];
}

return [
    // Additional modules to include when in development mode
    'modules' => $modules,
    // Configuration overrides during development mode
    'module_listener_options' => [
        'config_glob_paths' => [realpath(__DIR__).'/autoload/{,*.}{global,local}-development.php'],
        'config_cache_enabled' => false,
        'module_map_cache_enabled' => false,
    ],
];
