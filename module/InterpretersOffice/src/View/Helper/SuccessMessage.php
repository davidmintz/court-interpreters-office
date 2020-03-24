<?php
/** module/InterpretersOffice/src/View/Helper/SuccessMessage.php */

namespace InterpretersOffice\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * view helper for success-message div
 */
class SuccessMessage extends AbstractHelper
{

    /**
     * html template
     * @var string
     */
    protected $template = <<<EOT
    <div class="alert alert-success alert-dismissible border shadow-sm mx-auto mt-4" id="success-div" style="max-width:600px;%s">
        <h3></h3>
        <div id="success-message">%s</div>
        <button type="button" class="close" data-hide="alert" aria-label="close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
EOT;

    /**
     * renders div element for a success message
     *
     * @param  string $message
     * @return string
     */
    public function __invoke($message = null)
    {
        $html = sprintf(
            $this->template,
            $message ? '' : 'display:none',            
            $message ?: ''
        );

        return $html;
    }
}