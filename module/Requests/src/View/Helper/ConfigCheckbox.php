<?php
namespace InterpretersOffice\Requests\View\Helper;

use Zend\View\Helper\AbstractHelper;

class ConfigCheckbox extends AbstractHelper
{
    private $template = <<<TEMPLATE
    <div class="form-row form-check">
        <input type="hidden" name="%s" value="0">
        <input type="checkbox" %s id="%s" name="%s" value="1" class="form-check-input">
        <label for="%s" class="form-check-label text-left" >%s</label>
    </div>

TEMPLATE;

    public function __invoke($user_event,$language_type,$event_type,$action, $checked = true, $label = null)
    {
        $id =  "{$user_event}.{$event_type}.{$language_type}.{$action}";
        $element_name = "{$user_event}[${event_type}][{$language_type}][{$action}]";
        if (! $label) {
            $label = str_replace('-',' ',$action);
        }
        return sprintf($this->template,
                $element_name,// hidden field
                $checked ? 'checked' : '',
                $id, $element_name, $id, $label
            );
    }


}
