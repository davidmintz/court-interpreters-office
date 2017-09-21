<?php /** module/Admin/src/Form/View/Helper/InterpreterElementCollection.php */

namespace InterpretersOffice\Admin\Form\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Form\Element\Collection;

/**
 * helper for rendering interpreters
 */
class InterpreterElementCollection extends AbstractHelper
{
    /**
     * markup template
     * 
     * @var string
     */
    protected $template = 
        '<li class="list-group-item">'
        . '<input name="event[interpretersAssigned][%d][interpreter]" '
        . 'type="hidden" value="%d">'
        . '%s<div class="pull-right" title="remove this interpreter" class="remove-button">[x]</div></li>';
    
    /**
     * invoke
     * 
     * @param Collection $collection
     * @return string
     */
    public function __invoke(Collection $collection)
    {
        return $this->render($collection);
    }
    
    /**
     * renders markup
     * 
     * @param Collection $collection
     * @return string
     */
    public function render(Collection $collection)
    {
        $n = $collection->count();
        if (! $n) { return ''; }
        // to do: deal with possible undefined $form
        $form = $this->getView()->form;
        $entity = $form->getObject();
        $interpretersAssigned = $entity->getInterpretersAssigned();
        $i = 0;
        $markup = '';
        foreach ($interpretersAssigned as $interpEvent) {
            $interpreter = $interpEvent->getInterpreter();
            $name = $interpreter->getLastname().', '.$interpreter->getFirstName();
            $markup .= sprintf($this->template,$i,$interpreter->getId(),$name);
            $i++;
        }
        return '<ul class="list-group interpreters-assigned">'. $markup ."</ul>";
    }
    
    /**
     * gets template
     * 
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }    
}
