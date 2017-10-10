<?php

/** module/Admin/src/Form/EventFieldset.php */

namespace InterpretersOffice\Admin\Form;

use Zend\Form\Fieldset;
use Zend\Form\Element;
use Zend\InputFilter\InputFilterProviderInterface;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use InterpretersOffice\Form\ObjectManagerAwareTrait;
use InterpretersOffice\Form\Element\LanguageSelect;
use InterpretersOffice\Admin\Form\InterpretersAssignedFieldset;
use InterpretersOffice\Admin\Form\DefendantsEventFieldset;
use InterpretersOffice\Entity;
use DoctrineModule\Form\Element\ObjectSelect;

use InterpretersOffice\Entity\Judge;
/**
 * Fieldset for Event form
 * Notes to self: make a base class that adds only the elements required for 
 * a 'Request' form, and create a subclass 'Events' form (for admins) add the rest.
 */
class EventFieldset extends Fieldset implements InputFilterProviderInterface, 
        ObjectManagerAwareInterface        
{
     use ObjectManagerAwareTrait;

    /**
     * name of the form.
     *
     * @var string
     */
    protected $formName = 'event-form';

    /**
     * name of this Fieldset
     * @var string
     */
    protected $fieldset_name = 'event';

    /**
     * current user's role
     *
     * @var string
     */
    protected $auth_user_role;
    
    /**
     * constructor options
     * 
     * @var Array
     */
    protected $options;
    
    /**
     * fieldset elements
     * 
     * @var Array some of our element definitions
     */
    protected $elements = [
        [
            'name' => 'id',
            'type' =>  'Zend\Form\Element\Hidden',
        ],

        [
             'name' => 'date',
            //'type' => 'text',
            'type' => 'Zend\Form\Element\Date',
            'attributes' => [
                'id' => 'date',
                'class' => 'date form-control',
            ],
             'options' => [
                'label' => 'date',
                //'format' => 'm/d/Y',
                'format' => 'Y-m-d',
             ],
        ],
        [
            'name' => 'time',
            //'type' => 'text',
            'type' => 'Zend\Form\Element\Time',
            'attributes' => [
                'id' => 'time',
                'class' => 'time form-control',
            ],
             'options' => [
                'label' => 'time',
                'format' => 'H:i:s',// :s
             ],
        ],
        [
            'name' => 'docket',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => [
                'id' => 'docket',
                'class' => 'docket form-control',
            ],
             'options' => [
                'label' => 'docket',                
            ],            
        ],
        [
            'name' => 'defendant-search',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => [
                'id' => 'defendant-search',
                'class' => 'form-control',
                'placeholder' => 'last name[, first name]'
            ],
             'options' => [
                'label' => 'defendants',                
            ],                      
        ],

    ];


    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param array         $options
     */
    public function __construct(ObjectManager $objectManager, array $options)
    {
        if (! isset($options['action'])) {
            throw new \RuntimeException(
                'missing "action" option in EventFieldset constructor');
        }
        if (! in_array($options['action'], ['create', 'update','repeat'])) {
            throw new \RuntimeException('invalid "action" option in '
                . 'EventFieldset constructor: '.(string)$options['action']);
        }
        if (! isset($options['object'])) {
            $options['object'] = null;
        }
        /** might get rid of this... */
        if (isset($options['auth_user_role'])) {
            /** @todo let's not hard-code these roles */
            if (! in_array($options['auth_user_role'],
               ['anonymous','staff','submitter','manager','administrator'])) {
                throw new \RuntimeException(
               'invalid "auth_user_role" option in Event fieldset constructor');
            }
            $this->auth_user_role = $options['auth_user_role'];
        }
        $this->action = $options['action'];
        $this->options = $options;
        //unset($options['action']);

        parent::__construct($this->fieldset_name, $options);
        
        $this->objectManager = $objectManager;
        $this->setHydrator(new DoctrineHydrator($objectManager))
                ->setUseAsBaseFieldset(true);
        
        foreach ($this->elements as $element) {
            $this->add($element);
        }
        $this->add(
            new LanguageSelect(
                'language',
                [
                    'objectManager' => $objectManager,
                    'attributes'  => [
                        'id' => 'language',
                    ],
                    'options' => [
                        'label' => 'language', 
                        'empty_item_label' => '',
                    ],    
                ]
            )
        );        
        $this->addJudgeElements()
            ->addEventTypeElement()
            ->addLocationElements($options['object']);
        
        $interpretersAssignedFieldset = new InterpretersAssignedFieldset($objectManager);
        
        $this->add([
            'type' => Element\Collection::class,
            'name' => 'interpretersAssigned',
            'options' => [
                'label' => 'interpreters',
                'target_element' =>  $interpretersAssignedFieldset,                
            ],
        ]);
        $defendantsEventFieldset = new DefendantsEventFieldset($objectManager);
        
        $this->add([
            'type' => Element\Collection::class,
            'name' => 'defendantsEvent',
            'options' => [
                'label' => 'defendants',
                'target_element' =>   $defendantsEventFieldset,                
            ],
        ]);
        
        // figure out value options for interpreter select
        $empty_option =  ['value' => '','label'=>' ','attributes'=>['label'=>' ']];
        if ($options['object']) {
            $entity = $options['object'];
            $language_id = $entity->getLanguage()->getId();
            $repository = $objectManager->getRepository(Entity\Interpreter::class);
            $value_options = $empty_option +
                $repository->getInterpreterOptionsForLanguage($language_id);
        } else {
            $value_options = [$empty_option];
        }
        $this->add([
            'type'=>  Element\Select::class,
            'name'=> 'interpreter-select',
            'options' => [
                'label' => 'interpreter(s)',
                'value_options' => $value_options,
                'exclude' => true,
            ],
            'attributes' => [
                'class' => 'form-control', 
                'id' => 'interpreter-select',
            ],
            
        ]);
        if ("update" == $this->options['action']) {
            $this->add([
                'type'=> 'Hidden',
                'name'=> 'modified',            
            ]);
        }
        $this->addSubmitterElements($options['object']);
        // still to do: comments, admin comments, 
        // request meta (from whom, when)
        // end time ?
        
        // also sanity-check if there's an entity and one of its props is 
        // NOT in a select (e.g., a Judge marked inactive)
        $this->addSubmissionDateTimeElements();
        
    }
    
   /**
    * adds submitter elements
    * 
    * @param \InterpretersOffice\Entity\Event $event
    * @return \InterpretersOffice\Admin\Form\EventFieldset
    * @throws \Exception
    */
    public function addSubmitterElements(Entity\Event $event = null)
    {        
        $this->add([
            'type'=>'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'anonymousSubmitter',
            'options' => [
                'object_manager' => $this->getObjectManager(),
                'target_class' => Entity\Hat::class,
                'property' => 'name',
                'label' => 'submitted by',
                // the default is:'find_method' => ['name' => 'findAll'],
                'display_empty_item' => true,
                'empty_item_label' => '(title/description)',
                'option_attributes' => [
                    'data-can-be-anonymous' => 
                        function($hat) {
                            return $hat->getAnonymous() ? 1 : 0;
                        },
                ],
            ],         
            'attributes' => ['class' => 'form-control', 'id' => 'hat'],
        ]);
        $value_options = [['value' => '','label'=>'(person\'s name)',
            'attributes'=>['label'=>'person\'s name']]];
        $repo = $this->getObjectManager()->getRepository(Entity\Person::class);
        if ($event) {
            $hat = $event->getSubmitter() ? $event->getSubmitter()->getHat() :
                $event->getAnonymousSubmitter();
            if (! $hat) {
                throw new \Exception(sprintf(
                    'The database record for event id %d is in an invalid state: '
                  . 'both the submitter and generic submitter fields are null.',
                    $event->getId()
                ));
            }
            $value_options = array_merge($value_options, 
                    $repo->getPersonOptions($hat->getId()));
        }
        $this->add(
        [   'type'=>'Zend\Form\Element\Select',
            'name' => 'submitter',
            'options' => [
                'label' => '',
                'value_options' => $value_options,
            ],
            'attributes' => ['class' => 'form-control', 'id' => 'submitter'],
        ]);
        
        return $this;
    }
    
    /**
     * adds elements for date and time of submission
     * 
     * @param \InterpretersOffice\Entity\Event $event
     * @return \InterpretersOffice\Admin\Form\EventFieldset
     */
    public function addSubmissionDateTimeElements(Entity\Event $event = null)
    {
        $this->add(
        [
             'name' => 'submission_date',
            //'type' => 'text',
            'type' => 'Zend\Form\Element\Date',
            'attributes' => [
                'id' => 'submission_date',
                'class' => 'date form-control',
                'placeholder'=> 'date request was received',
            ],
             'options' => [
                'label' => 'requested on',
                'format' => 'Y-m-d',
            ]             
        ]);
        $this->add(
        [
            'name' => 'submission_time',
            //'type' => 'text',
            'type' => 'Zend\Form\Element\Time',
            'attributes' => [
                'id' => 'submission_time',
                'class' => 'time form-control',
                'placeholder'=> 'time request was received',
            ],
             'options' => [
                'label' => 'time',
                'format' => 'H:i:s',
             ],
        ]);
        return $this;
    }
    
    /**
     * adds the EventType element
     * 
     * @return \InterpretersOffice\Admin\Form\EventFieldset
     */
    public function addEventTypeElement()            
    {
        $repo = $this->objectManager->getRepository(Entity\EventType::class);
        $value_options = array_merge(
                [
                  ['label'=> ' ','value' =>'','attributes'=>['label'=> ' ']]  
                ],
                $repo->getEventTypeOptions()
         );
        $this->add(
        [
            'type'=>'Zend\Form\Element\Select',
            'name' => 'eventType',
            'options' => [
                'label' => 'event type',
                'value_options' => $value_options,
            ],
            'attributes' => ['class' => 'form-control', 'id' => 'event-type'],
        ]
        );
        
        return $this;
    }
    
    /**
     * adds Location elements
     * @param Entity\Event $event the Event instance, if we are updating
     * @return EventFieldset
     * @todo option grouping for sub-location?
     */
    public function addLocationElements(Entity\Event $event = null)
    {
        // the "parentLocation" element
        $this->add([
            'type'=>'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'parent_location',
            'options' => [
                'object_manager' => $this->getObjectManager(),
                'target_class' => 'InterpretersOffice\Entity\Location',
                'property' => 'name',
                'label' => 'place',
                'find_method' => [
                    'name' => 'getParentLocations'
                ],
                'display_empty_item' => true,
                'empty_item_label' => '(general location)',
                
            ],         
            'attributes' => [
                'class' => 'form-control', 
                'id' => 'parent_location'
            ],
        ]);
        
        // the (specific) "location" element
        $element_spec = [           
                'type' => 'Zend\Form\Element\Select',
                'name' => 'location',
                'options' =>[
                    'value_options' =>[],
                    'empty_option' => '(specific location)',                    
                ],                
                'attributes' => ['class' => 'form-control', 'id' => 'location'],
        ];
        if (! $event or ! $event->getLocation()) {
             $this->add($element_spec);
        } else { // the event location is set
           $parentLocation = $event->getLocation()->getParentLocation();
           if ($parentLocation) {
                $this->add([
                    'type'=>'DoctrineModule\Form\Element\ObjectSelect',
                    'name' => 'location',
                    'options' => [
                        'object_manager' => $this->getObjectManager(),
                        'target_class' => 'InterpretersOffice\Entity\Location',
                        'property' => 'name',
                        'find_method' => [
                            'name' => 'getChildren',
                            'params' => ['parent_id'=>$parentLocation->getId()]
                        ],
                        'display_empty_item' => true,
                        'empty_item_label' => '(specific location)',
                    ],         
                    'attributes' => 
                        ['class' => 'form-control', 'id' => 'location'],
                ]);
            } else {
                $this->add($element_spec);
            }
        }
        
        return $this;         
    }

    /**
     * adds the Judge element
     * 
     * @return \InterpretersOffice\Admin\Form\EventFieldset
     */
    public function addJudgeElements()
    {
        $repository = $this->getObjectManager()->getRepository(Judge::class);
        $value_options = 
                $repository->getJudgeOptions(['include_pseudo_judges'=>true]);
        array_unshift($value_options, 
                [ 'value' => '','label'=>' ','attributes'=>['label'=>' '] ]);
        $this->add([
            'type'=>'Zend\Form\Element\Select',
            'name' => 'judge',
            'options' => [
                'label' => 'judge',
                'value_options' => $value_options,
            ],
            'attributes' => ['class' => 'form-control', 'id' => 'judge'],
         
        ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Hidden',
                'name' => 'anonymousJudge',
                'attributes' => ['id' => 'anonymousJudge']
            ]
        );
        return $this;
    }

    /**
     * implements InputFilterProviderInterface
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        
        $spec = [
            'id' => [
                'required' => true,
                 'allow_empty' => true,
            ],
            'date' => [
                'required' => true,
                'allow_empty' => false,
                'validators'=> [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages' => [
                                'isEmpty' => 'date is required',
                            ],
                        ],
                    ],
                ],
            ],
            'time' => [
                'required' => true,
                'allow_empty' => true, // subject to conditions, to be continued
                'validators'=> [
                    // we need a format check here
                ],
            ],
            'location' => [
                'required'=> false, 
                 'allow_empty' => true,
                 'validators'=> [
                    
                ],
            ],
             'parent_location' => [
                 'required'=> false, 
                 'allow_empty' => true,
                 'validators'=> [
                    
                ],
            ],
            'language' => [
                'required' => true,
                'allow_empty' => false,
                'validators'=> [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages' => [
                                'isEmpty' => 'language is required',
                            ],
                        ],
                    ],
                ],
            ],
            'eventType' => [
                'required' => true,
                'allow_empty' => false,
                'validators'=> [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages' => [
                                'isEmpty' => 'event-type is required',
                            ],
                        ],
                    ],
                ],
            ],

            'judge' => [
                'required' => true,
                'allow_empty' => true,                
            ],
            'anonymousJudge' => [                
               'required' => true,
               'allow_empty' => true,                         
            ],
            'interpreter-select' => [
                'required' => false,
                'allow_empty' => true,
            ],
            'defendant-search' => [
                'required' => false,
                'allow_empty' => true,
            ],
            'hat' => [
                'required' => false,
                'allow_empty' => true,
            ],
        ];
        if ($this->has('modified')) {
            $spec['modified'] = [
                'required' => true,
                'allow_empty' => false,
                'validators'=> [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages' => [
                                'isEmpty' => 'form is missing last-modification timestamp',
                            ],
                        ],
                    ],
                ],
            ];
        }
        return $spec;
    }
   
}
