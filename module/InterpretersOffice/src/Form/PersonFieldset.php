<?php

/** module/InterpretersOffice/src/Form/PersonFieldset.php */

namespace InterpretersOffice\Form;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

use Zend\InputFilter;

//use DoctrineModule\Form\Element\ObjectSelect;

//use InterpretersOffice\Entity;

use Zend\Validator;

/**
 * Fieldset for Person entity.
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
                'id' => 'lastname',
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
                'id'   => 'firstname',
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
                'id' => 'middlename'
            ],
        ],
        'email' => [
            'type' => 'Zend\Form\Element\Text',
            'name' => 'email',
            'attributes' => [
                'class' => 'form-control',
                'id'    => 'email',
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
        'id' => [
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'id',
            'required' => true,
            'allow_empty' => true,
        ]
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
     * the action: either 'update' or 'create'
     * 
     * @var string
     */
    protected $action;
    
    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param array         $options
     */
    public function __construct(ObjectManager $objectManager, $options = [])
    {
        if (! isset($options['action'])) {
            throw new \RuntimeException('missing "action" option in PersonFieldset constructor');
        }
        if (! in_array($options['action'],['create','update'])) {
            throw new \RuntimeException('invalid "action" option in PersonFieldset constructor');   
        }
        $this->action = $options['action'];
        unset($options['action']);

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
        //$this->add($hat);
        //$input = new InputFilter\Input('hat');
       // $input->getValidatorChain()->attachByName('NotEmpty',[
       //     'messages' => ['isEmpty' => 'hat is required', ],
       //$factory = $this->factory->getInputFilterFactory();
        //$factory->
    }
    
    
    /**
     * returns specification for input filter (per interface).
     * 
     * @todo unique object validator thing for email
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        $spec = [
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
                'required' => false,
                'allow_empty' => true,
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
                'required' => false,
                'allow_empty' => true,
                'validators' => [
                    // 
                    //if we want to constrain the domain to values found in a 
                    //config, this would be a good place to set that up
                    // 
                    [
                        'name' => 'Zend\Validator\EmailAddress',
                        'options' => [
                            'messages' => [
                                Validator\EmailAddress::INVALID_FORMAT =>
                                    'invalid email address'
                            ],
                        ],
                        'break_chain_on_failure' => true,
                    ],
                    
                ],
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'Null'],
                ],
            ],
            'active' => [
                'validators' => [
                    [
                    'name' => 'NotEmpty',
                       'options' => [
                            'messages' => [
                                Validator\NotEmpty::IS_EMPTY => 
                                    '"active" setting is required'
                            ],
                        ]
                    ],
                    [
                    'name' => 'InArray',
                       'options' => [
                            'haystack' => [0,1],
                            'messages' => [
                                Validator\InArray::NOT_IN_ARRAY => 'invalid value for "active" field'
                            ],
                        ]
                    ],
                ],
            ],
            'officePhone' => [
                'required' => false,
                'allow_empty' => true,
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
                'required' => false,
                'allow_empty' => true,
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
        if ($this instanceof PersonFieldset) {
            $spec['hat'] = [
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [ 'messages' => ['isEmpty' => 'hat is required',], ],
                        'break_chain_on_failure' => true,
                    ],
                    [
                        'name' => 'InArray',
                        'options' => [ 

                            'messages' => [
                                Validator\InArray::NOT_IN_ARRAY => 'invalid value for hat',
                             ],
                            'haystack' => $this->getObjectSelectElementHaystack('hat'),
                         ],
                    ],
                ]
            ];
        }
        return $spec;
    }
    /**
     * provides a "haystack" out of a Doctrine ObjectSelect for InArray validator
     * 
     * @param string $elementName
     * @return Array
     */
    public function getObjectSelectElementHaystack($elementName = 'hat')
    {
        $data = $this->get($elementName)->getValueOptions();
        return  array_column($data,'value');
    }
}
