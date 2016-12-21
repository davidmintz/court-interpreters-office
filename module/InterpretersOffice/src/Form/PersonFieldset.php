<?php

/** module/InterpretersOffice/src/Form/PersonFieldset.php */

namespace InterpretersOffice\Form;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

//use DoctrineModule\Form\Element\ObjectSelect;

//use InterpretersOffice\Entity;

use Zend\Validator;

/**
 * Fieldset for Person entity. still incomplete.
 */
class PersonFieldset extends Fieldset implements InputFilterProviderInterface, ObjectManagerAwareInterface
{
    use ObjectManagerAwareTrait;

    /**
     * form elements.
     *
     * @var array
     */
    protected $elements = [

        'lastname' => [
            'type' => 'Zend\Form\Element\Text',
            'name' => 'lastname',
            'options' => [
                'label' => 'last name',
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ],
        'firstname' => [
            'type' => 'Zend\Form\Element\Text',
            'name' => 'firstname',
            'options' => [
                'label' => 'first name',
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ],
        'middlename' => [
            'type' => 'Zend\Form\Element\Text',
            'name' => 'middlename',
            'options' => [
                'label' => 'middle name/initial',
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ],
        'email' => [
            'type' => 'Zend\Form\Element\Text',
            'name' => 'email',
            'attributes' => [
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'email',
            ],
        ],
       
        'office_phone' => [
            'type' => 'Zend\Form\Element\Text',
            'name' => 'officePhone',
            'required' => true,
            'allow_empty' => true,
            'options' => [
                'label' => 'office phone',
            ],
             'attributes' => [
                'class' => 'form-control phone',
                'id' => 'officePhone',
             ],
            
        ],

         'mobile_phone' => [
            'type' => 'Zend\Form\Element\Text',
            'name' => 'mobilePhone',
            'required' => true,
            'allow_empty' => true,
            'options' => [
                'label' => 'mobile phone',
            ],
             'attributes' => [
                'class' => 'form-control phone',
                'id' => 'mobilePhone',
             ],
            
        ],


        'active' => [
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'active',
            'required' => true,
            'allow_empty' => false,
            'options' => [
                'label' => 'active',
                'use_hidden_element' => true,
                'checked_value' => 1,
                'unchecked_value' => 0,
            ],
            'attributes' => [
                'value' => 1,
            ]
        ],
    ];
    /**
     * name of the fieldset.
     * 
     * if we are a subclass, this needs to be overriden
     * 
     * @var string
     */
    protected $fieldset_name = 'person';
    
    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param array         $options
     */
    public function __construct(ObjectManager $objectManager, $options = [])
    {
        parent::__construct($this->fieldset_name, $options);
        $this->objectManager = $objectManager;
        $this->setHydrator(new DoctrineHydrator($objectManager))
                //->setObject(new Entity\Person())
                ->setUseAsBaseFieldset(true);
        foreach ($this->elements as $element) {
            $this->add($element);
        }
        
        $this->addHatElement();
        
    }
    
    /**
     * 
     * adds the Hat element to the form.
     * 
     * if we are a Person, we need the Hat element 
     * if we are a Judge, the Hat is pre-determined
     * if we are an Interpreter, there are only two kinds of hat
     * subclasses should override this to provide an appropriately configured
     * element 
     */
    public function addHatElement()
    {
        $this->add(
        [
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'hat',
            'required' => true,
            'allow_empty' => false,
            'options' => [
                'object_manager' => $this->objectManager,
                'target_class' => 'InterpretersOffice\Entity\Hat',
                'property' => 'name',
                'label' => 'hat',
                'display_empty_item' => true,
                'empty_item_label' => '',
                'find_method' => ['name' => 'getHatsForPersonForm'],

            ],
             'attributes' => [
                'class' => 'form-control',
                'id' => 'hat',
             ],
        ]);
        return $this;
    }
    /**
     * returns specification for input filter (per interface).
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return [
            'lastname' => [
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages' => [
                                'isEmpty' => 'last name is required',
                            ],
                        ],
                        'break_chain_on_failure' => true,
                    ],
                    [
                        'name' => 'InterpretersOffice\Form\Validator\ProperName',
                        'options' => ['type' => 'last'],

                    ],
                ],
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
            ],
            'firstname' => [
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages' => [
                                'isEmpty' => 'first name is required',
                            ],
                        ],
                        'break_chain_on_failure' => true,
                    ],
                    [
                        'name' => 'InterpretersOffice\Form\Validator\ProperName',
                        'options' => ['type' => 'first'],

                    ],
                ],
                'filters' => [
                    ['name' => 'StringTrim'],

                ],
            ],
            'middlename' => [
                'validators' => [
                    [
                        'name' => 'InterpretersOffice\Form\Validator\ProperName',
                        'options' => ['type' => 'middle'],
                    ],
                ],
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
            ],
            'email' => [
                'validators' => [
                    /** 
                    if we want to constrain the domain to values found in a 
                    config, this would be a good place to set that up
                     */
                    [
                        'name' => 'Zend\Validator\EmailAddress',
                        'options' => [
                            'messages' => [
                                Validator\EmailAddress::INVALID_FORMAT =>
                                    'invalid email address'
                            ],
                        ]
                    ],
                    
                ],
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
            ],
            'active' => [
                'validators' => [
                    /*
                    [
                      'name' => 'NotEmpty',
                       'options' => [
                            'messages' => [
                                Validator\NotEmpty::IS_EMPTY => 
                                    '"active" setting is required'
                            ],
                        ]
                    ],
                     */
                ],
            ],
            'officePhone' => [
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 10,
                            'max' => 10,
                            'messages' => [
                                Validator\StringLength::TOO_SHORT => 'phone number must contain ten digits',
                                Validator\StringLength::TOO_LONG => 'phone number cannot exceed ten digits',
                            ],
                        ],
                    ],
                ],
                'filters' => [
                    ['name' => 'StringTrim'],
                    [
                        'name' => 'Digits',
                    ],
                ],


            ],
            'mobilePhone' => [
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 10,
                            'max' => 10,
                            'messages' => [
                                Validator\StringLength::TOO_SHORT => 'phone number must contain ten digits',
                                Validator\StringLength::TOO_LONG => 'phone number cannot exceed ten digits',
                            ],
                        ],
                    ],
                ],
                'filters' => [
                    ['name' => 'StringTrim'],
                    [
                        'name' => 'Digits',
                    ],
                ],
            ],
        ];
    }
}
