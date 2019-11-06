<?php

// path to configuration
$path = 'module/Notes/config/config.json';
$config = json_decode(file_get_contents($path),true);
return ['notes' => $config];
