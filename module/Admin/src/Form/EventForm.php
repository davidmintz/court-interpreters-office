<?php
/** module/Admin/src/Form/EventForm.php */

namespace InterpretersOffice\Admin\Form;

use Zend\Form\Form as ZendForm;
use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Form\CsrfElementCreationTrait;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventInterface;

use Zend\Filter\Word\CamelCaseToUnderscore;

use Zend\Session\Container as Session;

/**
 * form for Event entity
 *
 */
class EventForm extends ZendForm implements ListenerAggregateInterface
{

     use CsrfElementCreationTrait;
     
     use ListenerAggregateTrait;
     
     

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
    
    protected $datetime_props = ['date','submission_datetime','time','end_time'];
    
    protected $state_before = [];
    
    /**
     *
     * @var \Zend\Session\Container
     */
    protected $session;
    
    /**
     *
     * @var CamelCaseToUnderscore
     */
    protected $camelCaseFilter;

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
        $this->session = new Session('EventManager');
       
        if (! is_array($this->session->timestamps)) {
            //printf("constructor initializing session timestamps... ");
            $this->session->timestamps = [];
        } 
        
    }
    
    public function attach(EventManagerInterface $events,$priority = 1)
    {
        $this->listeners[] = $events->attach('post.load',[$this, 'postLoad']);
        $this->listeners[] = $events->attach('pre.populate', [$this, 'prePopulate']);
        $this->listeners[] = $events->attach('post.validate',[$this, 'postValidate']);
        $this->listeners[] = $events->attach('pre.validate',[$this, 'preValidate']);        
    }
    
    public function postLoad(EventInterface $e)
    {
        
        $timestamps =& $this->session->timestamps;
        $entity = $e->getParam('entity');
        $id = $entity->getId();
        if (! isset($timestamps[$id])) {
            $timestamps[$id] = $entity->getModified()->format('Y-m-d H:i:s');
            //printf("<br>%s put shit in session: {$timestamps[$id]}",__FUNCTION__);
        }
        // store state of date/time fields for later comparison
        foreach ($this->datetime_props as $prop) {
            if (in_array($prop,['time','end_time'])) {
                $format = 'g:i a';
            } elseif ('submission_datetime'== $prop) {
                $format = 'Y-m-d H:i:s';
            } else {
                $format = 'm/d/Y';
            }
            if (strstr($prop, '_')) {
                $getter = 'get'.ucfirst(str_replace('_', '',$prop));
            } else {
                $getter = 'get'.ucfirst($prop);
            }
            $value = $entity->$getter();
            $this->state_before[$prop] = $value ? 
                    $value->format($format) : null;
        }
        //var_dump($this->state_before);
        
        return true;
    }
    /**
     * 
     * @return CamelCaseToUnderscore
     */
    protected function getFilter()
    {
        if (! $this->camelCaseFilter) {
            $this->camelCaseFilter = new CamelCaseToUnderscore();
        }
        return $this->camelCaseFilter;
    }
    
    public function postValidate(EventInterface $e)
    {
                
        $timestamps =& $this->session->timestamps;
        $entity = $this->getObject();
        $id = $entity->getId();
        if (!isset($timestamps[$id])) {
           throw new \Exception(
              "modification timestamp not found in the session for event id $id"
            );
        }
        $before = $timestamps[$id];
        $after = $entity->getModified()->format('Y-m-d H:i:s');
        //echo "<br>timestamp check:  <strong>$before</strong> vs <strong>$after</strong><br>";
        unset($timestamps[$id]);                
        return $before == $after;        
    }
    
   /**
    * preprocesses input and conditionally modifies validators
    * 
    * @param EventInterface $e
    * @return \InterpretersOffice\Admin\Form\EventForm
    */
    public function preValidate(EventInterface $e)
    {
        $input = $e->getParam('input');
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
            $event['submission_datetime'] = 
                (new \DateTime("$event[submission_date] $event[submission_time]"))
                    ->format("Y-m-d H:i:s");
        }
        if (isset($event['defendantNames'])) {
            $event['defendantNames'] = array_keys($event['defendantNames']);
        }
        // if this is NOT an update, we're done
        if (false === strstr($this->getAttribute('action'),'/edit/')) {
            $input->set('event',$event);
            return true;
        }
        /** 
         * test datetime properties for changes, and if there is no change, 
         * remove the element to stop Doctrine from insisting on updating anyway
         */
        foreach ($this->datetime_props as $prop) {
            if ($event[$prop] == $this->state_before[$prop]) {   
                //echo "$prop is now: {$event[$prop]}; was: {$this->state_before[$prop]}<br>";
                //echo "$prop DID NOT CHANGE. removing $field_name<br>";
                $this->get('event')->remove($prop);
            } else { echo "$prop has been modified... " ;}
        }
        $input->set('event',$event);
        
        return true;        
    }
   
    
    /**
     * processes form data before rendering
     * 
     * @return void
     */
    public function prePopulate(EventInterface $e)
    {
        
        $event = $this->getObject();
        
        
        
        $fieldset = $this->get('event');
        // if location is set and has a parent, set parent_location element
        $location = $event->getLocation();
        if ($location && $parentLocation = $location->getParentLocation()) {
            $fieldset->get('parent_location')->setValue($parentLocation->getId());
        }
        // seems like BULLSHIT that we have to do quite so much work here. 
        // am I doing something wrong that makes this necessary?
        
        // if submitter !== NULL, set anonymousSubmitter element = hat_id of submitter
        if (null !== $event->getSubmitter()) {
            $hat = $event->getSubmitter()->getHat();
            $fieldset->get('anonymousSubmitter')->setValue($hat->getId());
            // the form element value needs to be an integer, not an object.
            $fieldset->get('submitter')
                    ->setValue($event->getSubmitter()->getId());
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
        $submission_datetime_string = $fieldset->get('submission_datetime')
                ->getValue();
        if ($submission_datetime_string) {
            list($date,$time) = explode(' ',$submission_datetime_string);
            $fieldset->get('submission_date')->setValue($date);
            $fieldset->get('submission_time')->setValue($time);
        }
        
        //printf('<pre>%s</pre>',print_r($this->state_before,true));
        return true;
    }
}
