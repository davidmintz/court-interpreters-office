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

use Zend\InputFilter\InputFilterProviderInterface;



/**
 * form for Event entity
 *
 */
class EventForm extends ZendForm implements ListenerAggregateInterface,
     InputFilterProviderInterface   
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
    
    /**
     * date/time properties
     * 
     * @var array
     */
    protected $datetime_props = 
            ['date','time','end_time','submission_date', 'submission_time'];  
    
    /**
     * holds state of datetime fields
     * 
     * @var array
     */
    protected $state_before = [];
 
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
        /* putting this here instead of in the fieldset and handling the logic 
         * ourself saves us some pain 
         */
        if ("update" == $this->options['action']) {
            $this->add([
                'type'=> 'Hidden',
                'name'=> 'modified',  
                'attributes' => ['id' => 'modified'],
            ]);
        }
        $this->addCsrfElement();                
    }
    
    /**
     * implements ListenerAggregateInterface
     * 
     * @param EventManagerInterface $events
     * @param integer $priority
     */
    public function attach(EventManagerInterface $events,$priority = 1)
    {
        $this->listeners[] = $events->attach('post.load',[$this, 'postLoad']);
        $this->listeners[] = $events->attach('pre.populate', [$this, 'prePopulate']);
        $this->listeners[] = $events->attach('post.validate',[$this, 'postValidate']);
        $this->listeners[] = $events->attach('pre.validate',[$this, 'preValidate']);        
    }
    
    /**
     * Entity load event listener
     * 
     * Runs after entity is fetched, but before form data is set. We save the 
     * original DateTime instances for later comparison so we can avoid wasting
     * a needless update query.
     * 
     * @param EventInterface $e
     * @return void
     */
    public function postLoad(EventInterface $e)
    {        
        $entity = $e->getParam('entity');        
        foreach ($this->datetime_props as $prop) {            
            if (strstr($prop, '_')) {
                $getter = 'get'.ucfirst(str_replace('_', '',$prop));                
            } else {
                $getter = 'get'.ucfirst($prop);
            }                        
            $this->state_before[$prop] = $entity->$getter();
        }        
    }
   
    /**
     * resets datetime properties to their original instances, if unchanged
     * 
     * Checks whether date/time fields actual values been modified (not just the 
     * object instances), and restores them if they have not. We do this to stop 
     * Doctrine from wasting an update query when no data has actually changed.
     * 
     * @param EventInterface $e
     * @return void
     */
    public function postValidate(EventInterface $e)
    {
        $entity = $this->getObject();
        foreach ($this->datetime_props as $prop) {

            if (strstr($prop, '_')) {
                $getter = 'get'.ucfirst(str_replace('_', '',$prop));                
            } else {
                $getter = 'get'.ucfirst($prop);
            }            
            $new_value = $entity->$getter();
            $old_value = $this->state_before[$prop];
            if ($old_value == $new_value or
                (in_array($prop,['time','end_time','submission_time']) 
               && $new_value->format('g:i a')==$old_value->format('g:i a')
            )) {
                $setter = 's'.substr($getter,1);
                $entity->$setter($old_value);
            }                    
        }
    }
    
   /**
    * preprocesses input and conditionally modifies validators
    * 
    * @param EventInterface $e
    * @return void
    */
    public function preValidate(EventInterface $e)
    {
        $input = $e->getTarget()->getRequest()->getPost();        
        $event = $input->get('event');
        /* there is one form control for the judge but its value may 
         * correspond to either the 'judge' or the 'anonymousJudge' property,
         * and we have to make sure one is null and the other is not-null. Some
         * Javascript in the viewscript watches the judge element 'change' 
         * event and sets the is_anonymous_judge flag.
         */
        if (empty($event['judge']) && empty($event['anonymousJudge'])) {
            $validator = new \Zend\Validator\NotEmpty([
                'messages' => ['isEmpty' => "judge is required"],
                'break_chain_on_failure' => true,
            ]);
            $judge_input = $this->getInputFilter()->get('event')->get('judge');
            $judge_input->setAllowEmpty(false)->setRequired(true);
            $judge_input->getValidatorChain()->attach($validator);
            
        } elseif ($event['is_anonymous_judge']) {
            $event['anonymousJudge'] = $event['judge'];
            unset($event['judge']);
            $entity = $this->getObject();
            if ($entity->getJudge()) {
                $entity->setJudge(null);
            }
        }
        if (! empty($event['end_time'])) {
            $end_time_input = $this->getInputFilter()->get('event')
                    ->get('end_time');
            $end_time_input->getValidatorChain()->attach(new EndTimeValidator());            
        }
        // heads up:  setData() has yet to happen. therefore your elements
        // like anonymousSubmitter etc will be null 
        /** @todo untangle this and make error message specific to context */
        $anonSubmitterElement = $this->get('event')->get('anonymousSubmitter');
        $hat_options = $anonSubmitterElement->getValueOptions();
        $hat_id = $event['anonymousSubmitter'];
        // put the hat_id in the view -- to help persist submitter data
        // if validation fails
        $e->getTarget()->getViewModel()->hat_id = $hat_id;
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
        
        // if NO submitter but YES anonymous submitter, submitter = NULL
        if (empty($event['submitter']) && !empty($event['anonymousSubmitter'])) {
            $event['submitter'] = null;
            // printf("did we just fuck ourself at %d?<br>",__LINE__);
        // if YES submitter and YES anonymous submitter, anon submitter = NULL
        } elseif (!empty($event['submitter']) && !empty($event['anonymousSubmitter'])) {
            $event['anonymousSubmitter'] = null;
            // printf("did we just fuck ourself at %d?<br>",__LINE__);
        }

        if (isset($event['defendantNames'])) {
            $event['defendantNames'] = array_keys($event['defendantNames']);
        }
        $input->set('event',$event);        
       
    }
   
    
    /**
     * processes form data before rendering
     * 
     * @param EventInterface $e
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
        //$shit = $fieldset->get('date');
        //var_dump($shit->getValue());
        // this needs to be a string rather than an object
        /*
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
        }*/
        if ($this->has('modified')) {
            $date_obj = $event->getModified();
            if ($date_obj) {
                $this->get('modified')->setValue($date_obj->format('Y-m-d H:i:s'));
            }
        }
        //printf('<pre>%s</pre>',print_r($this->state_before,true));
        return true;
    }
    
    /**
     * implements InputFilterProviderInterface
     *
     * @return array
     */   
    function getInputFilterSpecification()
    {
        $spec = [];
        if (!$this->has('modified')) {
            return $spec;
        }
        
        $spec['modified'] = [
            'required' => true,
            'allow_empty' => false,
            'validators'=> [
                [
                    'name' => 'NotEmpty',
                    'options' => [
                        'messages' => [
                            'isEmpty' => 
                            'form is missing last-modification timestamp',
                        ],
                    ],
                ],
                [
                    'name' => 'Date',
                    'options' => [
                        'format' => 'Y-m-d H:i:s',
                        'messages' => [
                            \Zend\Validator\Date::INVALID_DATE =>
                                'invalid modification timestamp'
                        ],
                    ]
                ]
            ],
        ];        
        $em = $this->get('event')->getObjectManager();
        $spec['modified']['validators'][] = [            
            'name' => 'Callback',
            'options' => [
                'callback' => function($value,$context) use ($em) { 
                    $id = $context['event']['id'];                    
                    $dql = 'SELECT e.modified '
                            . 'FROM InterpretersOffice\Entity\Event e '
                            . 'WHERE e.id = :id';
                    $timestamp = $em->createQuery($dql)
                            ->setParameters(['id'=>$id])
                            ->getSingleScalarResult();
                    //echo "comparing $timestamp : $value";
                    return $timestamp == $value;
                },
                'messages' => [
                    \Zend\Validator\Callback::INVALID_VALUE =>
                        'Database record was modified by another process after '
                        . 'you loaded the form. In order to avoid overwriting '
                    . 'someone else\'s changes, please start over.',
                ],
            ]
            
        ];
        return $spec;
        
    }
    
    /**
     * is there a timestamp mismatch error?
     * 
     * @return boolean
     */
    function hasTimestampMismatchError()
    {
        $errors = $this->getMessages('modified');      
        return $errors && 
                key_exists(\Zend\Validator\Callback::INVALID_VALUE, $errors);
    }
}

class EndTimeValidator extends \Zend\Validator\AbstractValidator {
    
    protected $messageTemplates = [
        'invalid_format'=> 'invalid time',
        'invalid_time' => 'end time has to be later than start time',
        'missing_start_time'=> 
            'if end time is provided, start time is required',
        'is_future'=>'end time cannot be predicted for future events',
    ];
    public function isValid($value,$context = null) {                    
        if (! trim($value)) {
            return true;
        }
        if (isset($context['date']) && isset($context['time'])) {
            $datetime = strtotime("$context[date] $value");
            if ($datetime && time() < $datetime) {
                $this->error('is_future');
                return false;
            }

        }
        if ($value && ! $context['time']) {
            $this->error('missing_start_time');
            return false;
        }
        $end = strtotime($value);
        $start = strtotime($context['time']);
        if (false === $end) {
            $this->error('invalid_format');
            return false;
        }
        if ($start && $end && $end <= $start) {
            $this->error('invalid_time');
            return false;
        }                    
        return true;
    }
}