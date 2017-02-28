<?php
/**
 * module/Requests/src/Module.php.
 */

namespace InterpretersOffice\Requests;


/**
 * Module class for application's Requests module.
 */
class Module
{

    /**
     * returns this module's configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__.'/../config/module.config.php';
    }

}
