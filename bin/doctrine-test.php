<?php

$em = require('./doctrine-bootstrap.php');

printf("we have a %s\n",get_class($em));