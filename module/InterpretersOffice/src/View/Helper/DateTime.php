<?php
/** module/InterpretersOffice/src/View/Helper/DateTime.php */

namespace InterpretersOffice\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * helper for rendering \DateTime instances
 */
class DateTime extends AbstractHelper
{
    /**
     * renders PHP DateTime objects
     *
     * @param  string   $field name of field (property) holding the DateTime
     * @param  \DateTime $obj
     * @return string
     */
    public function __invoke($field, \DateTime $obj)
    {

        switch ($field) {
            case 'time':
            case 'end_time':
            case 'submission_time':
                $format = 'g:i a';
                break;
            case 'date':
            case 'submission_date':
                $format = 'd-M-Y';
                break;
            case 'modified':
            case 'last_updated':
            case 'created':
                $format = 'd-M-Y g:i a';
                break;
            default:
                $format = 'r';
        }

        return $obj->format($format);
    }
}
