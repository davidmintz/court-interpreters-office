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
    protected $template = <<<TEMPLATE
        <li class="list-group-item interpreter-assigned">
            <input name="event[interpreterEvents][%d][interpreter]" type="hidden" value="%d">
            <input name="event[interpreterEvents][%d][event]" type="hidden" value="%d">
            <input name="event[interpreterEvents][%d][createdBy]" type="hidden" value="">
             %s            
            <button class="btn btn-danger btn-xs btn-remove-item pull-right" title="remove this interpreter">
            <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
            <span class="sr-only">remove this interpreter
            </button>
        </li>           
TEMPLATE;
    
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
        
        if (! $collection->count()) { return ''; }
        // to do: deal with possible undefined $form
        $form = $this->getView()->form;
        $entity = $form->getObject();
        $interpreterEvents = $entity->getInterpreterEvents();
        $markup = '';
        foreach ($interpreterEvents as $i => $interpEvent) {
            $interpreter = $interpEvent->getInterpreter();
            $event = $interpEvent->getEvent();
            $name = $interpreter->getLastname().', '.$interpreter->getFirstName();
            $markup .= sprintf($this->template,
                    $i, $interpreter->getId(),
                    $i, $event->getId(),
                    $i, 
                    $name);          
        }
        return $markup;
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
    public function fromArray(Array $data)
    {
        if (! isset($data['name'])) {
            $data['name'] = '__NAME__';
        }
        $markup = sprintf($this->template,
                $data['index'],
                $data['interpreter_id'],
                $data['index'],
                $data['event_id'],
                $data['index'],
                $data['name']);
        return $markup;
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
            'interpreter_id' => [
                'name'=> 'interpreter_id',
                'required' => true,
                'allow_empty' => false,
                'validators' => [
                    ['name'=>'Digits'],
                ],
            ],
              'event_id' => [
                'name'=> 'event_id',
                'required' => true,
                'allow_empty' => true,
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
