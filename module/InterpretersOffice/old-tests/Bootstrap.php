<?php

namespace ApplicationTest;

class Bootstrap
{
    // maybe later
}
putenv('APP_ENV=testing');
/*
FixtureManager::start();
$script = __DIR__.'/data/sqlite.schema.sql';
$db = FixtureManager::getEntityManager()->getConnection();
$shit = $db->executeQuery(file_get_contents($script));
etc
 */
