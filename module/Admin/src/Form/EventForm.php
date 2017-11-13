<?php
/** module/Admin/src/Form/EventForm.php */

namespace InterpretersOffice\Admin\Form;

use Zend\Form\Form as ZendForm;
use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Form\CsrfElementCreationTrait;
use Zend\Stdlib\Parameters;

/**
 * form for Event entity
 *
 */
class EventForm extends ZendForm
{

     use CsrfElementCreationTrait;

     /**
     * name of Fieldset class to instantiate and add to the form.
     *
     * subclasses can override this with the classname
     * of a Fieldset that extends EventFieldset
     *
     * @var string
     */
    protected $fieldsetClass = EventFieldset::class;

    /**
     * name of the form
     *
     * @var string
     */
    protected $formName = 'event-form';

     /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param array         $options
     */
    public function __construct(ObjectManager $objectManager, $options = null)
    {
        parent::__construct($this->formName, $options);
        $fieldset = new $this->fieldsetClass($objectManager, $options);
        $this->add($fieldset);
        $this->addCsrfElement();
        
    }
   /**
    * preprocesses input and conditionall modifies validators
    * 
    * @param Parameters $input
    * @return \InterpretersOffice\Admin\Form\EventForm
    */
    public function preValidate(Parameters $input)
    {
        $event = $input->get('event');
        if (!$event['judge'] && empty($event['anonymousJudge'])) {
            $validator = new \Zend\Validator\NotEmpty([
                'messages' => ['isEmpty' => "judge is required"],
                'break_chain_on_failure' => true,
            ]);
            $judge_input = $this->getInputFilter()->get('event')->get('judge');
            $judge_input->setAllowEmpty(false);
            $judge_input->getValidatorChain()->attach($validator);
        }
        
        /** @todo untangle this and make error message specific to context */
        $anonSubmitterElement = $this->get('event')->get('anonymousSubmitter');
        $hat_options = $anonSubmitterElement->getValueOptions();
        $hat_id = $anonSubmitterElement->getValue();        
        $key = array_search($hat_id, array_column($hat_options, 'value'));
        $can_be_anonymous = (!$key) ? false : 
                $hat_options[$key]['attributes']['data-can-be-anonymous'];
        //var_dump($hat_options[$key]['attributes']['data-can-be-anonymous']);
        if ((empty($event['submitter']) && empty($event['anonymousSubmitter'])) 
                or
            (!$can_be_anonymous  && empty($event['submitter']))
        ) {
            $validator = new \Zend\Validator\NotEmpty([
                'messages' => 
                    [ 'isEmpty' => 
                        "identity or description of submitter is required"],
                'break_chain_on_failure' => true,
            ]);
            $submitter_input = $this->getInputFilter()->get('event')->get('submitter');
            $submitter_input->setAllowEmpty(false);
            $submitter_input->getValidatorChain()->attach($validator);            
        }
        // end to-do ///////////////////////////////////////////////////////////
        
        // if NO submitter but YES anonymous submitter, submitter = NULL
        if (empty($event['submitter']) && !empty($event['anonymousSubmitter'])) {
            $event['submitter'] = null;
        // if YES submitter and YES anonymous submitter, anon submitter = NULL
        } elseif (!empty($event['submitter']) && !empty($event['anonymousSubmitter'])) {
            $event['anonymousSubmitter'] = null;
        }
        if (!empty($event['submission_date']) && !empty($event['submission_time'])) {            
            $event['submission_datetime'] = "$event[submission_date] $event[submission_time]";
        }
        if (isset($event['defendantNames'])) {
            $event['defendantNames'] = array_keys($event['defendantNames']);
        } 
        /** @todo the thing to do here is test datetime properties for changes, 
         and if there is no change, flat-out remove the element to stop Doctrine
         from insisting on updating anyway
         */
        //$this->get('event')->remove('date');
        $input->set('event',$event);
        return $this;
        
    }
    
    public function postValidate()
    {
        
    }
}
