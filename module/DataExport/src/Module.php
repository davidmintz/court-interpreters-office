<?php /** module/DataExport/src/Module.php */

namespace SDNY\DataExport;

/**
 * Module
 */
class Module {
 /**
     * returns this module's configuration.
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__.'/../config/module.config.php';
    }
}