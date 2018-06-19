<?php
/** module/Admin/src/Form/View/Helper/DefendantNameElementCollection.php */

namespace InterpretersOffice\Admin\Form\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Helper\EscapeHtml;

/**
 * helper for rendering defendant-name
 */
class DefendantNameElementCollection extends AbstractHelper
{

     /**
     * markup template
     *
     * @var string
     */
    protected $__template = <<<TEMPLATE
        <li class="list-group-item defendant py-1">
            <input name="event[defendantNames][%d]" type="hidden" value="%s">
            <span data-id="%d" class="align-middle">%s</span>
            <button class="btn btn-warning btn-sm btn-remove-item float-right border" title="remove this defendant">
                <span class="fas fa-times" aria-hidden="true"></span>
                <span class="sr-only">remove this defendant</span>
            </button>
        </li>
TEMPLATE;

/**
 * markup template
 *
 * @var string
 */
protected $template = <<<TEMPLATE
    <li class="list-group-item py-1 interpreter-assigned">
        <input name="event[defendantsEvents][%d][defendant]" type="hidden" value="%d">
        <input name="event[defendantsEvents][%d][event]" type="hidden" value="%d">
        <input name="event[defendantsEvents][%d][defendantName]" type="hidden" value="%s">
        <span class="align-middle">%s</span>
        <button class="btn btn-warning btn-sm btn-remove-item float-right border" title="remove this defendant">
        <span class="fas fa-times" aria-hidden="true"></span>
        <span class="sr-only">remove this interpreter
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

        return sprintf($this->template, $id, $label, $id, $label);
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

    /**
     * renders markup
     *
     * @param Collection $collection
     * @return string
     */
    public function render(ElementCollection $collection)
    {
        /** THIS IS TOO COMPLICATED. re-think and start over. */

        if (! $collection->count()) {
            return '';
        } // really?
        // to do: deal with possible undefined $form
        $form = $this->getView()->form;
        $entity = $form->getObject();
        $defendantsEvents = $entity->getDefendantsEvents();
        $markup = '';
        foreach ($defendantsEvents as $i => $de) {
            $defendant = $de->getDefendantName();
            $event = $de->getEvent();
            $name = $defendant->__toString();
            // 7 placeholders, excessive!
            $markup .= sprintf(
                $this->template,
                $i,
                $defendant->getId(),
                $i,
                $event->getId(),
                $i,
                $name,
                $name // [sic]
            );
        }
        return $markup;
    }
}
