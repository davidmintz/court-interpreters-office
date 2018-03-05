<?php
/** module/InterpretersOffice/src/View/Helper/DefendantName.php */

namespace InterpretersOffice\Form\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Helper\EscapeHtml;

/**
 * helper for rendering defendant-name
 */
class DefendantName extends AbstractHelper
{

     /**
     * markup template
     *
     * @var string
     */
    protected $template = <<<TEMPLATE
        <li class="list-group-item defendant py-1">
            <input name="event[defendant_names][%d]" type="hidden" value="%s">
            <span class="align-middle">%s</span>            
            <button class="btn btn-warning btn-sm btn-remove-item float-right border" title="remove this defendant">
            <span class="fas fa-times" aria-hidden="true"></span>
            <span class="sr-only">remove this defendant
            </button>
        </li>            
TEMPLATE;

    /**
     * EscapeHtml escaper
     *
     * @var EscapeHtml
     */
    protected $escaper;

    /**
     * renders markup
     *
     * @param integer $id
     * @param string $name
     * @return string
     */
    public function __invoke($id, $name)
    {
        $escaper = $this->escaper->getEscaper();
        $label = $escaper->escapeHtml($name);
        //$shit = print_r(get_class_methods(),true);
        //printf("<pre>%s</pre>",$shit);
        return sprintf($this->template, $id, $label, $label);
    }

    /**
     * constructor
     *
     * @param EscapeHtml $escaper
     */
    public function __construct(EscapeHtml $escaper)
    {
        $this->escaper = $escaper;
    }
}
