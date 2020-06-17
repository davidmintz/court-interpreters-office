<?php /** module/Admin/src/View/Helper/EmailSalutation.php */

namespace InterpretersOffice\Admin\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * helper to cut down repetition in email templates
 */
class EmailSalutation extends AbstractHelper
{

    /**
     * invokes
     * 
     * @return string|null
     */
    public function __invoke()
    {
        $view = $this->getView();
        if ($view->salutation !== null) {
            // non-automated
            if (!$view->salutation) {
                return ;
            } else {
                $salutation = $view->salutation;
            }
        } else {
            // automated message 
            $address = $view->to;
            if (!empty($address['name'])):
                $salutation = "Dear {$address['name']}:";
            elseif ($view->interpreter): 
            // if (is_object($this->interpreter) && $this->interpreter instanceof \InterpretersOffice\Entity\Interpreter) :
                $salutation = sprintf('Dear %s:',$view->interpreter->getFullName());
            else:
                $salutation = 'Hello,';
            endif;
        }

        return "<p>$salutation</p>";
    }
}