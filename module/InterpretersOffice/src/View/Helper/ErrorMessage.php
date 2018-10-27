<?php
/** module/InterpretersOffice/src/View/Helper/ErrorMessage.php */

namespace InterpretersOffice\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * view helper for error-message div
 */
class ErrorMessage extends AbstractHelper
{

    protected $template = <<<EOT
    <div class="alert alert-warning alert-dismissible border shadow-sm mx-auto" id="error-div" style="max-width:600px;%s">
        <h3>system error</h3>
        <div id="error-message">%s</div>
        <button type="button" class="close" data-hide="alert" aria-label="close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
EOT;

    /**
     * renders div element for an error message
     * @param  string $message
     * @return string
     */
    public function __invoke($message = null)
    {
        $html = sprintf($this->template,
            $message ? '' : 'display:none',
            $message ?: '');

        return $html;
    }
}
