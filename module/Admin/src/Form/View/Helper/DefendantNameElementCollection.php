<?php
/** module/Admin/src/Form/View/Helper/DefendantNameElementCollection.php */

namespace InterpretersOffice\Admin\Form\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Form\Element\Collection as ElementCollection;

class DefendantNameElementCollection extends AbstractHelper
{
    /**
     * markup template
     *
     * @var string
     */
    protected $template = <<<TEMPLATE
        <li class="list-group-item py-1 defendant">
            <input name="event[defendantsEvents][%d][defendant]" type="hidden" value="%d">
            <input name="event[defendantsEvents][%d][event]" type="hidden" value="%d">
            <input name="event[defendantsEvents][%d][name]" type="hidden" value="%s">
            <span class="align-middle">%s</span>
            <button class="btn btn-warning btn-sm btn-remove-item float-right border" title="remove this defendant">
            <span class="fas fa-times" aria-hidden="true"></span>
            <span class="sr-only">remove this defendant
            </button>
        </li>
TEMPLATE;

    /**
     * invoke
     *
     * @param ElementCollection $collection
     * @return string
     */
    public function __invoke(ElementCollection $collection)
    {
        return $this->render($collection);
    }

    public function render(ElementCollection $collection)
    {
        if (! $collection->count()) {
            return '';
        }
        $markup = '';
        $deftEvents = $this->getView()->form->getObject()->getDefendantsEvents();
        foreach ($deftEvents as $i => $de) {
            $name = $this->getView()->escapeHtml($de->getDefendantName());
            $markup .= sprintf($this->template,
                $i, $de->getDefendantName()->getId(),
                $i, $de->getEvent()->getId(),
                $i, $name,$name
            );
        }
        return $markup;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function fromArray(Array $data)
    {
        $i = $data['index'];
        $name = $this->getView()->escapeHtml($data['name']);
        return sprintf($this->template,
            $i, $data['defendant'],
            $i, $data['event'],
            $i, $name,$name
        );
    }

}
