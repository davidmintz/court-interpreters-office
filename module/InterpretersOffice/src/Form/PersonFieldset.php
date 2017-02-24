<?php

/** module/InterpretersOffice/src/Form/PersonFieldset.php */

namespace InterpretersOffice\Form;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use InterpretersOffice\Form\Validator\NoObjectExists as NoObjectExistsValidator;
use InterpretersOffice\Form\Validator\UniqueObject;
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
                'id' => 'firstname',
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
                'id' => 'middlename',
            ],
        ],
        'email' => [
            'type' => 'Zend\Form\Element\Text',
            'name' => 'email',
            'attributes' => [
                'class' => 'form-control',
                'id' => 'email',
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
                'id' => 'person-active',
            ],
        ],
        'id' => [
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'id',
            'required' => true,
            'allow_empty' => true,
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
     * the action: either 'update' or 'create'.
     *
     * @var string
     */
    protected $action;

    /**
     * role of the currently authenticated user
     *
     * @var string
     */
    protected $auth_user_role = 'anonymous';
    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param array         $options
     */
    public function __construct(ObjectManager $objectManager, $options = [])
    {
        if (!isset($options['action'])) {
            throw new \RuntimeException('missing "action" option in PersonFieldset constructor');
        }
        if (!in_array($options['action'], ['create', 'update'])) {
            throw new \RuntimeException('invalid "action" option in PersonFieldset constructor');
        }
        
        if (isset($options['auth_user_role'])) {
            /** @todo let's not hard-code these roles */
             if (! in_array($options['auth_user_role'],['anonymous','staff','submitter','manager','administrator'])) {
                  throw new \RuntimeException('invalid "auth_user_role" option in PersonFieldset constructor');
             }
             $this->auth_user_role = $options['auth_user_role'];
        }
        $this->action = $options['action'];
        unset($options['action']);
        //printf('DEBUG action is %s in PersonFieldset line %d<br>',$this->action,__LINE__);
        $use_as_base_fieldset = isset($options['use_as_base_fieldset']) ? $options['use_as_base_fieldset'] : true;
        parent::__construct($this->fieldset_name, $options);
        $this->objectManager = $objectManager;
        $this->setHydrator(new DoctrineHydrator($objectManager, true))
                //->setObject(new Entity\Person())
                ->setUseAsBaseFieldset($use_as_base_fieldset);
        foreach ($this->elements as $element) {
            $this->add($element);
        }

        $this->addHatElement();
    }

    /**
     * adds the Hat element to the form.
     *
     * If we are a Person, we need the Hat element
     * If we are a Judge, the Hat is pre-determined
     * If we are an Interpreter, there are only two kinds of Hat.
     * If we are in the context of User form, the options populating the 
     * depend on the role of the authenticated user and (possibly) the 
     * controller action.
     * Subclasses should override this to provide an appropriately configured
     * Hat select element
     */
    public function addHatElement()
    {
        // if we are the base fieldset, it's a Person form, or a subclass;
        // otherwise, we are in the context of a User form 
        $form_context = $this->useAsBaseFieldset ? 'person' : 'user';  
        if ($form_context == 'person') {
            $find_method = ['name'=>'getHatsForPersonForm'];
        } else {
            $find_method = [
                'name' => 'getHatsForUserForm',
                'params'=> [
                    'auth_user_role'=>$this->auth_user_role,
                    'action'=> $this->action,
                ]
            ];
        }
        $this->add(
            [
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'hat',
            'options' => [
                'object_manager' => $this->objectManager,
                'target_class' => 'InterpretersOffice\Entity\Hat',
                'property' => 'name',
                'label' => 'hat',
                'display_empty_item' => true,
                'empty_item_label' => '',
                'find_method' =>$find_method,
             ],
             'attributes' => [
                'class' => 'form-control',
                'id' => 'hat',
             ],
            ]
        );
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

                    //if we want to constrain the domain to values found in a
                    //config, this would be a good place to set that up

                    [
                        'name' => 'Zend\Validator\EmailAddress',
                        'options' => [
                            'messages' => [
                                Validator\EmailAddress::INVALID_FORMAT => 'invalid email address',
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
                'required' => false,
                'allow_empty' => true,
                'validators' => [
                    [
                    'name' => 'NotEmpty',
                       'options' => [
                            'messages' => [
                                Validator\NotEmpty::IS_EMPTY => '"active" setting is required',
                            ],
                        ],
                    ],
                    [
                    'name' => 'InArray',
                       'options' => [
                            'haystack' => [0, 1],
                            'messages' => [
                                Validator\InArray::NOT_IN_ARRAY => 'invalid value for "active" field',
                            ],
                        ],
                    ],
                ],
                ///*
                'filters' => [
                    [
                        'name'=>'Zend\Filter\Boolean'
                    ],
                ],
                //*/
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

        // validators for Hat element depend on class of current instance
        if (get_class($this) == self::class) {
            $spec['hat'] = [

                'required' => true,
                'allow_empty' => false,
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => ['messages' => ['isEmpty' => 'hat is required']],
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
                ],
            ];
        }
        // options common to all scenarios
        $validatorOptions = [
            'object_repository' => $this->objectManager->getRepository('InterpretersOffice\Entity\Person'),
            'object_manager' => $this->objectManager,
            'use_context' => true,
        ];

        if ('create' == $this->action) {
            // use the NoObjectExists validator
            $validatorClass = NoObjectExistsValidator::class;
            $validatorOptions['messages'] = [
                NoObjectExistsValidator::ERROR_OBJECT_FOUND => 'a person with this "Hat" and email address is already in your database',
            ];
            // .. for the hat and email fields
            $validatorOptions['fields'] = ['hat', 'email'];

            $spec['email']['validators'][] = [
                'name' => $validatorClass,
                'options' => $validatorOptions,
                'break_chain_on_failure' => true,
            ];
             // ... and for the active and email fields
            $validatorOptions['fields'] = ['active', 'email'];
            $validatorOptions['messages'] = [
                NoObjectExistsValidator::ERROR_OBJECT_FOUND => 'there is already a person in your database with this email address and "active" setting',
            ];
            $spec['email']['validators'][] = [
                'name' => $validatorClass,
                'options' => $validatorOptions,
                'break_chain_on_failure' => true,
            ];
        } else { // action is update, use the UniqueObject validator
            
            //printf('DEBUG action is %s in PersonFieldset line %d<br>',$this->action,__LINE__);
            $validatorClass = UniqueObject::class;

            $validatorOptions['messages'] = [
                UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 'there is already a person in your database with this email address and "active" setting',
            ];
            $validatorOptions['fields'] = ['hat', 'email'];

            $spec['email']['validators'][] = [
                'name' => $validatorClass,
                'options' => $validatorOptions,
                'break_chain_on_failure' => true,
            ];

            // ... and again, for the active and email fields
            $validatorOptions['messages'] = [
                UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 'there is already a person with this "hat" and email address in your database',
            ];
            $validatorOptions['fields'] = ['active', 'email'];
            $spec['email']['validators'][] = [
                'name' => $validatorClass,
                'options' => $validatorOptions,
                'break_chain_on_failure' => true,
            ];
        }

        return $spec;
    }
    /**
     * gets a "haystack" out of a Doctrine ObjectSelect
     * for use by an InArray validator.
     *
     * @param string $elementName
     *
     * @return array
     */
    public function getObjectSelectElementHaystack($elementName = 'hat')
    {
        $data = $this->get($elementName)->getValueOptions();

        return  array_column($data, 'value');
    }
}
