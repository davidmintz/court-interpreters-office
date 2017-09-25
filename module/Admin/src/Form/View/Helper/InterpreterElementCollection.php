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
        . '%s<div class="remove-button pull-right" title="remove this interpreter">[x]</div></li>';
    
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
    
    public function wrap($markup)
    {
        return '<ul class="list-group interpreters-assigned">'. $markup ."</ul>";
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
        return $this->wrap($markup);
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
    
    /**
     * renders Interpreter fieldset from array data
     * 
     * @param array $data
     * @return string
     */
    public function renderFromArray(Array $data)
    {
        if (! isset($data['name'])) {
            $data['name'] = '__NAME__';
        }
        $markup = sprintf($this->template,$data['index'],$data['id'],$data['name']);
        return $this->wrap($markup);
    }
    
    /**
     * input filter spec for xhr/interpreter template helper
     * 
     * @return Array
     */
    public function getInputFilterSpecification()
    {
         return 
         [   
            'id' => [
                'name'=> 'id',
                'required' => true,
                'allow_empty' => false,
                'validators' => [
                    ['name'=>'Digits'],
                ],
            ],
            'index' => [
                'name' => 'index',
                'required' => true,
                'allow_empty' => false,
                'validators' => [
                    ['name'=>'Digits'],
                ]
            ],
            'name' => [
                'name' => 'name',
                'required' => false,
                'allow_empty' => true,
                'validators' => [
                    [ 'name'=>'StringLength',
                        'options' => [
                            'max' => 152,
                            'min' => 5,
                        ],
                    ],
                ],
            ],
        ];
    }
}
