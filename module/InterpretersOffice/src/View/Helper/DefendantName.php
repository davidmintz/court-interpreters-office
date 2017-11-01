<?php
/** module/InterpretersOffice/src/View/Helper/DefendantName.php */

namespace InterpretersOffice\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Helper\EscapeHtml;

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

    
    public function __invoke($id,$name)
    {               
        $escaper = $this->escaper->getEscaper();
        $label = $escaper->escapeHtml($name);
        //$shit = print_r(get_class_methods(),true);
        //printf("<pre>%s</pre>",$shit);
        return sprintf($this->template,$id,$label,$label);
    }

    public function __construct(EscapeHtml $escaper)
    {
        $this->escaper= $escaper;
    }
    
}