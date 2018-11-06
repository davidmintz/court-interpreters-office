<?php
namespace InterpretersOffice\Requests\View\Helper;

use Zend\View\Helper\AbstractHelper;

class ConfigCheckbox extends AbstractHelper
{
    private $template = <<<TEMPLATE
    <div class="form-row form-check">
        <input type="checkbox" %s id="%s" name="%s" value="1" class="form-check-input">
        <label for="%s" class="form-check-label" >%s</label>
    </div>

TEMPLATE;

    public function __invoke($event,$interp_type,$action, $checked = true, $label = null)
    {
        $id =  "{$event}.{$interp_type}.{$action}";
        $element_name = "{$event}[{$interp_type}][{$action}]";
        if (! $label) {
            $label = str_replace('-',' ',$action);
        }
        return sprintf($this->template,
                $checked ? 'checked' : '',
                $id, $element_name, $id, $label
            );
    }


}
