<?php
/** module/Admin/src/Form/View/Helper/DefendantElementCollection.php */

namespace InterpretersOffice\Admin\Form\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Form\Element\Collection as ElementCollection;

/**
 * form helper for displaying DefendantEvent entities
 */
class DefendantElementCollection extends AbstractHelper
{
    /**
     * markup template
     *
     * @var string
     */
    protected $template = <<<TEMPLATE
        <li class="list-group-item py-1 defendant">
            <input class="defendant_id" name="event[defendantEvents][%d][defendant]" type="hidden" value="%d">
            <input class="event_id" name="event[defendantEvents][%d][event]" type="hidden" value="%d">
            <input class="defendant_name" name="event[defendantEvents][%d][name]" type="hidden" value="%s">
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

    /**
     * renders DefendantEvent entities
     *
     * @param  ElementCollection $collection
     * @return string $markup finished HTML
     */
    public function render(ElementCollection $collection)
    {
        if (! $collection->count()) {
            return '';
        }
        $markup = '';
        $deftEvents = $this->getView()->form->getObject()->getDefendantEvents();
        foreach ($deftEvents as $i => $de) {
            $name = $this->getView()->escapeHtml($de->getDefendant());
            $markup .= sprintf($this->template,
                $i, $de->getDefendant()->getId(),
                $i, $de->getEvent()->getId(),
                $i, $name,$name
            );
        }
        return $markup;
    }

    /**
     * gets the HTML template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * renders a single DefendantEvent from array data
     * 
     * @param  Array  $data
     * @return string
     */
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
