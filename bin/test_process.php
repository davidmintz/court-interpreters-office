#!/usr/bin/env php
<?php

$file = __DIR__.'/../data/progress.txt';

if (!  file_exists($file)) {
    touch($file);
}
echo "running";
for ($i = 0; $i < 150; $i++) {
    $i++;
    $fp = fopen($file,'w');
    fputs($fp, "$i of 150");
    fclose($fp);
    usleep(250*1000);
}
