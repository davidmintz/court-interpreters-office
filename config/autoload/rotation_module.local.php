<?php

// path to configuration
$path = 'module/Rotation/config/config.json';
$config = json_decode(file_get_contents($path),true);
return ['rotation' => $config];
