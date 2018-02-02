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
use InterpretersOffice\Entity;

use Zend\Validator\Callback;
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
      * type to use for time elements
      *
      * for convenience, while trying to make up our mind about
      * using the HTML5 date and time elements
      *
      * @var string
      */
     const TIME_ELEMENT_TYPE = 'Text';
      /**
      * type to use for date elements
      *
      * @var string
      */
     const DATE_ELEMENT_TYPE = 'Text';

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
     * action
     *
     * @var string
     */
    protected $action;

    /**
     * fieldset elements
     *
     * @var Array some of our element definitions
     */
    protected $elements = [
        [
            'name' => 'id',
            'type' =>  'Zend\Form\Element\Hidden',
            'attributes' => ['id' => 'event_id'],
        ],

        [
             'name' => 'date',
            //'type' => 'text',
            'type' => self::DATE_ELEMENT_TYPE,
            'attributes' => [
                'id' => 'date',
                'class' => 'date form-control',
                'placeholder'=> '(required)',
            ],
             'options' => [
                'label' => 'date',
                //'format' => 'm/d/Y',
                //'format' => 'Y-m-d',
             ],
        ],
        [
            'name' => 'time',
            //'type' => 'text',
            'type' =>self::TIME_ELEMENT_TYPE,
            'attributes' => [
                'id' => 'time',
                'class' => 'time form-control',
            ],
             'options' => [
                'label' => 'time',
               //    'format' => 'H:i:s',// :s
             ],
        ],
        [
            'name' => 'docket',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => [
                'id' => 'docket',
                'class' => 'docket form-control',
                 'placeholder'=> '(strongly recommended)',
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
                        'class' => 'custom-select text-muted'
                    ],
                    'options' => [
                        'label' => 'language',
                        'empty_item_label' => '(required)',
                    ],
                ]
            )
        );
        $this->addJudgeElements()
            ->addEventTypeElement()
            ->addLocationElements($options['object']);

        $interpreterEventsFieldset = new InterpreterEventsFieldset($objectManager);
        $this->add([
            'type' => Element\Collection::class,
            'name' => 'interpreterEvents',
            'options' => [
                'label' => 'interpreters',
                'target_element' =>  $interpreterEventsFieldset,
            ],
        ]);
        /* defendant names, not actually displayed */
        $this->add([
            'name' => 'defendantNames',
            'type' => 'Zend\Form\Element\Select',
            'options' => [
                //'empty_option' => '',
                'value_options' => [],
                'disable_inarray_validator' => true,
                'label' => 'defendants',
            ],
            'attributes' => [
                'style' => 'display:none',
                'id' => 'deft-select',
                'multiple' => 'multiple',
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
                'class' => 'form-control custom-select',
                'id' => 'interpreter-select',
            ],

        ]);

        $this->addSubmitterElements($options['object']);

        $this->add([
            'type' => 'Textarea',
            'name' => 'comments',
            'attributes' => [
                'class' => 'form-control',
                'id' => 'comments',
                'rows' => 2,
                'cols' => 28,
            ],
            'options' => [
                'label' => 'comments (public)',
            ],

        ]);
        $this->add([
            'type' => 'Textarea',
            'name' => 'admin_comments',
            'attributes' => [
                'class' => 'form-control',
                'id' => 'admin_comments',
                'rows' => 2,
                'cols' => 28,
            ],
            'options' => [
                'label' => 'comments (private)'
            ],

        ]);
        /** @to do make this configurable */
        $this->add(
         [
            'name' => 'end_time',
            //'type' => 'text',
            'type' => self::TIME_ELEMENT_TYPE,
            'attributes' => [
                'id' => 'end_time',
                'class' => 'time form-control',
            ],
             'options' => [
                'label' => 'time',
                'format' => 'H:i:s',// :s
             ],
        ]);
        /** @todo also:  sanity-check if there's an entity and one of its props
         * is NOT in a select (e.g., a Judge marked inactive)
         */

        $this->addSubmissionDateTimeElements();


        // reason for cancellation
        $repository = $objectManager->getRepository(Entity\Event::class);
        $cancellation_options = $repository->getCancellationOptions();
        $default_label = 'N/A';
        $default_opt = ['label' => $default_label,'value'=> '',
            'attributes' => ['label'=>$default_label,'class'=>'cancellation-default'],
        ];
        array_unshift($cancellation_options, $default_opt);
        $this->add([
            'name' => 'cancellationReason',
            'type' => 'select',
             'attributes' => [
                'id'   => 'cancellation_reason',
                'class' => 'form-control custom-select',
             ],
             'options' => [
                'label' => 'cancellation',
                'value_options' => $cancellation_options,
             ],

        ]);

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
            'attributes' => ['class' => 'form-control custom-select', 'id' => 'hat'],
        ]);
        $empty_option = [['value' => '','label'=>'(person\'s name)',
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
            $value_options = $repo->getPersonOptions($hat->getId());
            array_unshift($value_options, $empty_option);
        } else {
            $value_options = $empty_option;
        }
        $this->add(
        [   'type'=>'Zend\Form\Element\Select',
            'name' => 'submitter',
            'options' => [
                'label' => '',
                'value_options' => $value_options,
            ],
            'attributes' => ['class' => 'form-control custom-select', 'id' => 'submitter'],
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
            'type' => self::DATE_ELEMENT_TYPE,
            'attributes' => [
                'id' => 'submission_date',
                'class' => 'date form-control',
                'placeholder'=> 'date request was received',
            ],
             'options' => [
                'label' => 'requested on',
                //'format' => 'Y-m-d',
            ]
        ]);
        $this->add(
        [
            'name' => 'submission_time',

            'type' => self::TIME_ELEMENT_TYPE,
            'attributes' => [
                'id' => 'submission_time',
                'class' => 'time form-control',
                'placeholder'=> 'time request was received',
            ],
             'options' => [
                'label' => 'time',
                //'format' => 'H:i:s',
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
                  ['label'=> '(required)','value' =>'',
                      'attributes'=>['label'=> '(required)']
                  ]
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
            'attributes' => ['class' => 'custom-select text-muted', 'id' => 'event-type'],
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
                'class' => 'form-control custom-select text-muted',
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
                'attributes' => ['class' => 'form-control custom-select text-muted', 'id' => 'location'],
        ];
        if (! $event or ! $event->getLocation()) {
             $this->add($element_spec);
        } else { // the event location is set
           $location = $event->getLocation();
           $parentLocation = $location->getParentLocation();
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
            } else { // really?
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
                [ 'value' => '','label'=>'(required)','attributes'=>['label'=>' '] ]);
        $this->add([
            'type'=>'Zend\Form\Element\Select',
            'name' => 'judge',
            'options' => [
                'label' => 'judge',
                'value_options' => $value_options,
            ],
            'attributes' => ['class' => 'form-control custom-select text-muted', 'id' => 'judge'],

        ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Hidden',
                'name' => 'is_anonymous_judge',
                'attributes' => ['id' => 'is_anonymous_judge'],
            ]
        );
        $this->add(
            [
                'type' => 'Zend\Form\Element\Hidden',
                'name' => 'anonymousJudge',
                'attributes' => ['id' => 'anonymousJudge'],
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
            'interpreterEvents' => [
                'required' => false, 'allow_empty' => true,
            ],
            'defendantNames'  => [
                'required' => false, 'allow_empty' => true,
            ],
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
                        'break_chain_on_failure' => true,
                    ],
                ],
            ],
            'time' => [
                'required' => true,
                'allow_empty' => true, // subject to conditions, to be continued
                'validators'=> [
                    // we need a format check here
                    [
                        'name'=>  Callback::class,
                        'options'=> [
                            'callback'=> function($value) {
                                return strtotime($value) !== false;
                            } ,
                            'messages'=> [
                               Callback::INVALID_VALUE => 'invalid time',
                            ],
                        ],
                        'break_chain_on_failure' => true,
                    ]
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
                'required' => false,
                'allow_empty' => true,
            ],
            'is_anonymous_judge' => [
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
            'docket' => [
                'required' => true,
                'allow_empty' => true,
                'filters' => [
                    ['name'=>Filter\Docket::class,],
                ],
                'validators' => [
                    [ 'name' => Validator\Docket::class, ]
                ],
            ],
            'anonymousSubmitter' => [
                'required' => true,
                'allow_empty' => true,
            ],
            'submitter' => [
                'required' => true,
                'allow_empty' => true,// conditionally
            ],
            'end_time' => [
                'required' => false,
                'allow_empty' => true,
            ],
            'comments' => [
                'required' => false,
                'allow_empty' => true,
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 5,
                            'max' => 600,
                            'messages' => [
                            \Zend\Validator\StringLength::TOO_LONG =>
                                'maximum length allowed is 600 characters',
                             \Zend\Validator\StringLength::TOO_SHORT =>
                                'minimum length allowed is 5 characters',
                            ]
                        ]
                    ]
                ],
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
            ],
             'admin_comments' => [
                'required' => false,
                'allow_empty' => true,
                 'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 5,
                            'max' => 600,
                            'messages' => [
                            \Zend\Validator\StringLength::TOO_LONG =>
                                'maximum length allowed is 600 characters',
                             \Zend\Validator\StringLength::TOO_SHORT =>
                                'minimum length allowed is 5 characters',
                            ]
                        ]
                    ]
                ],
                'filters' => [
                    ['name' => 'StringTrim'],
                ],

            ],
            'cancellationReason' => [
                'required' => true,
                'allow_empty'=> true,
            ],
        ];
        if (true) { // enable|disable temporarily
            foreach (['submission_date', 'submission_time'] as $field) {
                $label = str_replace('_', ' ', $field);
                $shit = [
                    'required' => true,
                    'allow_empty' => false,
                    'validators' => [
                        [
                            'name' => 'NotEmpty',
                            'options' => [
                                'messages' => [
                                    'isEmpty' => "$label is required"
                                ],
                            ],
                            'break_chain_on_failure' => true,
                        ],
                    ],
                ];
                $spec[$field] = $shit;
            }
            $spec['submission_time']['validators'][] = new Validator\EventSubmissionDateTime();
        }
        return $spec;
    }

}
