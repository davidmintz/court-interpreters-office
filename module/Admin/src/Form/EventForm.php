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
    * preprocesses input and conditionally modifies validators
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
        // heads up:  setData() has yet to happen. therefore your elements
        // like anonymousSubmitter etc will be null 
        /** @todo untangle this and make error message specific to context */
        $anonSubmitterElement = $this->get('event')->get('anonymousSubmitter');
        $hat_options = $anonSubmitterElement->getValueOptions();
        $hat_id = $event['anonymousSubmitter'];
        $key = array_search($hat_id, array_column($hat_options, 'value'));
        
        $can_be_anonymous = (!$key) ? false : 
                $hat_options[$key]['attributes']['data-can-be-anonymous'];
        //echo "can be anonymous? " ;var_dump((boolean)$can_be_anonymous);
        //printf("did you just fuck yourself at %d?<br>",__LINE__);
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
            // printf("did you just fuck yourself at %d?<br>",__LINE__);
        // if YES submitter and YES anonymous submitter, anon submitter = NULL
        } elseif (!empty($event['submitter']) && !empty($event['anonymousSubmitter'])) {
            $event['anonymousSubmitter'] = null;
            // printf("did you just fuck yourself at %d?<br>",__LINE__);
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
    
    
    /**
     * processes form data before rendering
     * 
     * @return void
     */
    public function prePopulate()
    {
        /** 
            some data needs to be reset, reformatted, moved around
        */
        $event = $this->getObject();
        $fieldset = $this->get('event');
        // if location is set and has a parent, set parent_location element
        $location = $event->getLocation();
        if ($location && $parentLocation = $location->getParentLocation()) {
            $fieldset->get('parent_location')->setValue($parentLocation->getId());
        }
        // OK, it is BULLSHIT that we have to do all this. am I doing something wrong 
        // that makes this necessary?
        // 
        // if submitter !== NULL, set anonymousSubmitter element = hat_id of submitter
        if (null !== $event->getSubmitter()) {
            $hat = $event->getSubmitter()->getHat();
            $fieldset->get('anonymousSubmitter')->setValue($hat->getId());
            // the form element value needs to be an integer, not an object.
            $fieldset->get('submitter')->setValue($event->getSubmitter()->getId());
        }
        $judge_element = $fieldset->get('judge');
        // judge element value needs to be an integer
        $judge = $fieldset->get('judge')->getValue();
        if (is_object($judge)) {
           $fieldset->get('judge')->setValue($judge->getId());
        }
        // if the anonymousJudge property is not null,
        $anonymous_judge = $event->getAnonymousJudge();
        if (is_object($anonymous_judge)) {
            // set the judge element accordingly
            $judge_element->setValue($anonymous_judge->getId());
        }
        // this needs to be a string
        $submission_datetime = $fieldset->get('submission_datetime')->getValue();
        if (is_object($submission_datetime)) {
             $fieldset->get('submission_datetime')
                 ->setValue($submission_datetime->format('Y-m-d H:i:s'));     
        }
        // and now that it's a string, split it into two fields
        $submission_datetime_string =  $fieldset->get('submission_datetime')->getValue();
        if ($submission_datetime_string) {
            list($date,$time) = explode(' ',$submission_datetime_string);
            $fieldset->get('submission_date')->setValue($date);
            $fieldset->get('submission_time')->setValue($time);
        }
    }
}
