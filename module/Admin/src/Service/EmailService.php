<?php
/** module/InterpretersOffice/src/Service/EmailService.php */

declare(strict_types=1);

namespace InterpretersOffice\Admin\Service;

use InterpretersOffice\Service\EmailTrait;

class EmailService
{
    use EmailTrait;

    /**
     * configuration
     *
     * @var Array
     */
    private $config;

    /**
     * constructor
     *
     * @param Array $config
     */
    function __construct(Array $config)
    {
        $this->config = $config;
    }

}
