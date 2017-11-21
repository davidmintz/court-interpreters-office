<?php
/** module/InterpretersOffice/src/View/Helper/DefendantName.php */

namespace InterpretersOffice\View\Helper;

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
        <li class="list-group-item defendant">
            <input name="event[defendantNames][%d]" type="hidden" value="%s">
             %s            
            <button class="btn btn-danger btn-xs btn-remove-item pull-right" title="remove this defendant">
            <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
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
    public function __invoke($id,$name)
    {               
        $escaper = $this->escaper->getEscaper();
        $label = $escaper->escapeHtml($name);
        //$shit = print_r(get_class_methods(),true);
        //printf("<pre>%s</pre>",$shit);
        return sprintf($this->template,$id,$label,$label);
    }
    
    /**
     * constructor
     * 
     * @param EscapeHtml $escaper
     */
    public function __construct(EscapeHtml $escaper)
    {
        $this->escaper= $escaper;
    }
    
}