<?php
namespace InterpretersOffice\Requests\View\Helper;

use Zend\View\Helper\AbstractHelper;

class ConfigCheckbox extends AbstractHelper
{
    private $template = <<<TEMPLATE
    <div class="form-row form-check">
        %s
        <label for="%s" class="form-check-label text-left" >%s</label>
    </div>
TEMPLATE;

    public function __invoke($element)
    {
        $rendered_element = $this->getView()->formElement($element);
        $id = $element->getAttribute('id');
        $label = $element->getLabel();

        return sprintf($this->template, $rendered_element,$id,$label);
    }


}
