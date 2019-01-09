<?php
/** module/Requests/src/View/Helper/ConfigCheckbox.php */

namespace InterpretersOffice\Requests\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Form\ElementInterface;

/**
 * helper for checkbox element for Request event-listener config form
 */
class ConfigCheckbox extends AbstractHelper
{
    /**
     * template for checkbox element
     * @var string
     */
    private $template = <<<TEMPLATE
    <div class="form-row form-check">
        %s
        <label for="%s" class="form-check-label text-left" >%s</label>
    </div>
TEMPLATE;

    /**
     * __invoke
     *
     * @param  Zend\Form\ElementInterface $element
     * @return string HTML
     */
    public function __invoke(ElementInterface $element)
    {
        $rendered_element = $this->getView()->formElement($element);
        $id = $element->getAttribute('id');
        $label = $element->getLabel();

        return sprintf($this->template, $rendered_element, $id, $label);
    }
}
