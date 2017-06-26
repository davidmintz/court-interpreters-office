<?php
/**
 * module/Admin/src/Form/InterpreterFieldset.php.
 */

namespace InterpretersOffice\Admin\Form;

use InterpretersOffice\Form\PersonFieldset;
use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Entity;

// experimental
use Zend\Form\Element;

/**
 * InterpreterFieldset.
 *
 * @author david
 */
class InterpreterFieldset extends PersonFieldset
{
    /**
     * name of the fieldset.
     *
     * since we are a subclass of PersonFieldset, this needs to be overriden
     *
     * @var string
     */
    protected $fieldset_name = 'interpreter';
    
    /**
     * configuration options
     * 
     * @var Array options
     */
    protected $options;

   /**
     * encrypted field values
     *
     * encrypted ssn and dob are stored here so we can compare
     * and determine whether to apply validation 
     *
     * @var Array
     */ 
    protected $original_encrypted_values;

    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param array         $options
     */
    public function __construct(ObjectManager $objectManager, $options = [])
    {
        parent::__construct($objectManager, $options);
        
        $this->options = $options;
        /*
        // could not get this to hydrate properly, so we're not using
        // Element\Collection with a InterpreterLanguageFieldset
        $this->add([
            'type' => Element\Collection::class,
            'name' => 'interpreterLanguages',
            'options' => [
                'label' => 'working languages',
               // 'count' => 2,
                'should_create_template' => false,
                'allow_add' => true,
                'allow_remove' => true,
                'target_element' => new InterpreterLanguageFieldset($objectManager),
            ],
        ]);
        */
        $this->add(
            new \InterpretersOffice\Form\Element\LanguageSelect(
                'language-select',
                [
                    'objectManager' => $objectManager,
                    'option_attributes' => ['data-certifiable' => function (Entity\Language $language) {
                        return $language->isFederallyCertified() ? 1 : 0;
                    }],
                ]
            )
        );

        $this->add([
            'type' => 'Select',
            'name' => 'interpreter-languages',

            'attributes' => [
                'multiple' => 'multiple',
                'class' => 'hidden',
            ],
            'options' => [
                'value_options' => [],
                'disable_inarray_validator' => true,
                'use_hidden_element' => true,
            ],
        ]);
        // fingerprint date
        $this->add(
        [
             'name' => 'fingerprintDate',
            //'type' => 'text',
            'type' => 'Zend\Form\Element\Date',
            'attributes' => [
                //'required' => 'required',
                //'size' => 15,
                'id' => 'fingerprint_date',
                'class' => 'date form-control',
            ],
            'options' => [
                'label' => 'fingerprinted on',
                //'format' => 'm/d/Y',
                'format' => 'Y-m-d',
            ],
        ]);
        
        // security clearance date
        $this->add(
        [
             'name' => 'securityClearanceDate',
            //'type' => 'text',
            'type' => 'Zend\Form\Element\Date',
            'attributes' => [
                //'required' => 'required',
                //'size' => 15,
                'id' => 'security_clearance_date',
                'class' => 'date form-control',
                 'placeholder' => 'date clearance was received',
            ],
            'options' => [
                'label' => 'security clearance date',
                //'format' => 'm/d/Y',
                'format' => 'Y-m-d',
            ],
        ]);
        // contract expiration date
        $this->add(
        [
             'name' => 'contractExpirationDate',
            //'type' => 'text',
            'type' => 'Zend\Form\Element\Date',
            'attributes' => [
                //'required' => 'required',
                //'size' => 15,
                'id' => 'contract_expiration_date',
                'class' => 'date form-control',
            ],
            'options' => [
                'label' => 'contract expiration date',
                //'format' => 'm/d/Y',
                'format' => 'Y-m-d',
            ],
        ]);
        // date oath taken
        $this->add(
        [
             'name' => 'oathDate',
            //'type' => 'text',
            'type' => 'Zend\Form\Element\Date',
            'attributes' => [
                //'required' => 'required',
                //'size' => 15,
                'id' => 'oath_date',
                'class' => 'date form-control',
            ],
            'options' => [
                'label' => 'oath taken',
                //'format' => 'm/d/Y',
                'format' => 'Y-m-d',
            ],
        ]);
        // home phone
        $this->add([
            'name' => 'homePhone',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => [ 'id'=> 'home_phone', 'class' => 'form-control phone'],
            'options' => [ 'label' => 'home phone'],
        ]);
        
        $this->add([
            'name' => 'comments',
            'type' => 'Zend\Form\Element\Textarea',
            'attributes' => [ 
                'id'=> 'comments', 'class' => 'form-control',
                'rows' => 10,
                'cols'  => 36,
             ],
            'options' => [ 'label' => 'comments'],            
        ]);
        
        $this->addAddressElements();
        
        if ($options['vault_enabled']) {        
            // complicated stuff
            $this->add(
            [
                'name' => 'dob',
                'type' => 'Zend\Form\Element\Text',
                'attributes' => ['id' => 'dob','class'=>'form-control encrypted date'],
                'options' => ['label' => 'date of birth'],
            ]);
            $this->add(
            [
                'name' => 'ssn',
                'type' => 'Zend\Form\Element\Text',
                'attributes' => ['id' => 'ssn','class'=>'form-control encrypted'],
                'options' => ['label' => 'social security number'],
            ]);
        }
    }
    /**
     * adds the specialized "Hat" element to the form.
     *
     * @return \InterpretersOffice\Admin\Form\InterpreterFieldset
     */
    

    public function setOriginalEncryptedValues()
    {

        //$this->original_encryted_values = $data;
    }


    public function addHatElement()
    {
        $this->add([
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'hat',
            'options' => [
                'object_manager' => $this->objectManager,
                'target_class' => 'InterpretersOffice\Entity\Hat',
                'property' => 'name',
                'find_method' => ['name' => 'getInterpreterHats'],
                'label' => 'hat',
            ],
             'attributes' => [
                'class' => 'form-control',
                'id' => 'hat',
             ],
        ]);
        // hack designed to please HTML5 validator
        $element = $this->get('hat');
        $options = $element->getValueOptions();
        array_unshift($options, [
           'label' => ' ',
           'value' => '',
           'attributes' => [
               'label' => ' ',
           ],
        ]);
        $element->setValueOptions($options);

        return $this;
    }
    /**
     * adds address elements
     */
    public function addAddressElements()
    {
        // address 1
        $this->add([
            'name' => 'address1',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => [ 'id'=> 'address1', 'class' => 'form-control'],
            'options' => [ 'label' => 'address (1)',],
        ]);
        // address 2
        $this->add([
            'name' => 'address2',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => [ 'id'=> 'address2', 'class' => 'form-control',],
            'options' => [ 'label' => 'address (2)'],
        ]);
        
        // city
        $this->add([
            'name' => 'city',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => [ 'id'=> 'city', 'class' => 'form-control'],
            'options' => [ 'label' => 'city'],
        ]);
        
        // state or province
        $this->add([
            'name' => 'state',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => [ 'id'=> 'state', 'class' => 'form-control'],
            'options' => [ 'label' => 'state'],
        ]);
        // zip/postal code
        $this->add([
            'name' => 'zip',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => [ 'id'=> 'zip', 'class' => 'form-control'],
            'options' => [ 'label' => 'zip/postal code'],
        ]);
        // country
        $this->add([
            'name' => 'country',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => [ 'id'=> 'country', 'class' => 'form-control'],
            'options' => [ 'label' => 'country'],
        ]);
    }
    
    /**
     * overrides parent implementation of InputFilterProviderInterface.
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {       
        $spec = parent::getInputFilterSpecification();
        $language_options = $this->get('language-select')->getValueOptions();

        // require users to provide yes|no for federal-certified language
        // which we already know from the language select > option elements'
        // 'certifiable' attribute
        $certifiable = array_column($language_options, 'attributes', 'value');

        $spec['interpreter-languages'] = [

            'allow_empty' => false,
            'required' => true,
            'validators' => [
                 [
                    'name' => 'NotEmpty',
                    'options' => [
                        'messages' => [
                            'isEmpty' => 'at least one language is required',
                        ],
                    ],
                    'break_chain_on_failure' => true,
                 ],
                [   // backdoor method for ensuring 'federalCertification' field
                    // is set, if appropriate: ignore the $value and inspect the
                    // $context array
                    'name' => 'Callback',
                    'options' => [
                        'callback' => function ($value, $context) use ($certifiable) {
                            $languages_submitted = $context['interpreter-languages'];
                            foreach ($languages_submitted as $language) {
                                $id = $language['language_id'];
                               // should never happen unless they are messing with us
                                if (! isset($language['federalCertification'])) {
                                    return false;
                                }
                                $submitted_cert = 
                                        in_array($language['federalCertification'],[0,1]) ?
                                       (bool) $language['federalCertification'] : null;
                                $cert_required = (bool) $certifiable[$id]['data-certifiable'];
                                if ($cert_required && ! is_bool($submitted_cert)) {
                                    return false;
                                }
                            }

                            return true;
                        },
                        'messages' => [
                            \Zend\Validator\Callback::INVALID_VALUE => 'yes/no required for federal certification',
                        ],
                    ],
                ],
            ],
        ];

        // this one is just for the UI, not part of the entity's data
         $spec['language-select'] = [
            'required' => true,
            'allow_empty' => true,
         ];
         $spec['hat'] = [
                'validators' => [
                    [
                    'name' => 'NotEmpty',
                    'options' => [
                        'messages' => [
                            'isEmpty' => 'hat is required (contract or staff)',
                        ],
                    ],
                    'break_chain_on_failure' => true,
                    ],
                ],
         ];
         // @todo:  major validation stuff !
         
         // dates
         $spec['fingerprintDate'] = [
             'allow_empty' => true,
                    'required' => false,
                    'filters' => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                    'validators' => [
                        [
                            'name' => 'Zend\Validator\Date',
                            'options' => [
                                'format' => 'm/d/Y',
                                'messages' => [\Zend\Validator\Date::INVALID_DATE=>'valid date in MM/DD/YYYY format is required']
                            ],
                            'break_chain_on_failure' => true,
                        ],
                        [ 'name' => 'Callback',
                            'options' => [
                                'callback' => function ($value, $context) {
                                    // it can't be in the future
                                    // and it can't be unreasonably long ago
                                    list($M, $D, $Y) = explode('/', $value);
                                    $date = "$Y-$M-$D";
                                    $max = date('Y-m-d');
                                    $min = (new \DateTime("-3 years"))->format('Y-m-d');                                    
                                    return $date >= $min && $date <= $max;
                                    
                                },
                                'messages' => [
                                    \Zend\Validator\Callback::INVALID_VALUE => 'date has to be between three years ago and today',
                                ],
                            ],
                        ],
                    ]
                ];
            $spec['securityClearanceDate'] = [
             'allow_empty' => true,
             'required'  => false,
             'filters' => [
                [
                    'name' => 'StringTrim',
                ],
              ],
             'validators' => [
                 [
                    'name'=> 'Zend\Validator\Date',
                    'options'=>[
                        'format' => 'm/d/Y',
                         'messages' => [\Zend\Validator\Date::INVALID_DATE=>'valid date in MM/DD/YYYY format is required']
                    ],
                    'break_chain_on_failure' => true,
                 ],
                 [ 'name' => 'Callback',
                        'options' => [
                            'callback' => function ($value, $context) {
                                // it can't be in the future
                                // and it can't be unreasonably long ago
                                list($M, $D, $Y) = explode('/', $value);
                                $date = "$Y-$M-$D";
                                $max = date('Y-m-d');
                                $min = (new \DateTime("-5 years"))->format('Y-m-d');                                    
                                return $date >= $min && $date <= $max;
                            },
                            'messages' => [
                                \Zend\Validator\Callback::INVALID_VALUE => 'date has to be between five years ago and today',
                            ],
                        ],
                    ],
                ],
         ];
         $spec['contractExpirationDate'] = [
            'allow_empty' => true,
            'required' => false,
            'filters' => [
                [
                    'name' => 'StringTrim',
                ],
            ],
            'validators' => [
                [
                    'name' => 'Zend\Validator\Date',
                    'options' => [
                        'format' => 'm/d/Y',
                        'messages' => [\Zend\Validator\Date::INVALID_DATE => 'valid date in MM/DD/YYYY format is required']
                    ],
                    'break_chain_on_failure' => true,
                ],
                [ 'name' => 'Callback',
                    'options' => [
                        'callback' => function ($value, $context) {
                            list($M, $D, $Y) = explode('/', $value);
                            $date = "$Y-$M-$D";
                            $max = date('Y-m-d');
                            $min = (new \DateTime("-5 years"))->format('Y-m-d');
                            return $date >= $min && $date <= $max;
                        },
                        'messages' => [
                            \Zend\Validator\Callback::INVALID_VALUE => 'date has to be between five years ago and today',
                        ],
                    ],
                ],
            ],
        ];
        $spec['oathDate'] = [
            'allow_empty' => true,
            'required'  => false,
            'filters' => [
                [
                    'name' => 'StringTrim',
                ],
            ],
             'validators' => [
                [
                    'name' => 'Zend\Validator\Date',
                    'options' => [
                        'format' => 'm/d/Y',
                        'messages' => [\Zend\Validator\Date::INVALID_DATE => 'valid date in MM/DD/YYYY format is required']
                    ],
                    'break_chain_on_failure' => true,
                ],
                [ 'name' => 'Callback',
                    'options' => [
                        'callback' => function ($value, $context) {
                            // it can't be in the future
                            // and it can't be unreasonably long ago
                            list($M, $D, $Y) = explode('/', $value);
                            $date = "$Y-$M-$D";
                            $max = (new \DateTime("+2 years"))->format('Y-m-d');
                            $min = (new \DateTime("-5 years"))->format('Y-m-d');
                            return $date >= $min && $date <= $max;
                        },
                        'messages' => [
                            \Zend\Validator\Callback::INVALID_VALUE => 'date has to be between five years ago and two years from today',
                        ],
                    ],
                ],
            ],        
         ];
         // encrypted fields        
         $spec['dob'] = [
             'allow_empty' => true,
             'required'  => false,
              'filters' => [
                [
                    'name' => 'StringTrim',
                ],
            ],
             'validators' => [
                 [
                    'name'=> 'Zend\Validator\Date',
                    'options'=>[
                        'format' => 'Y-m-d',
                         'messages' => [\Zend\Validator\Date::INVALID_DATE=>'valid date in MM/DD/YYYY format is required']
                    ],
                    'break_chain_on_failure' => true,
                ],
                [ 'name' => 'Callback',
                    'options' => [
                        'callback' => function ($date, $context) {
                            // it can't be in the future
                            // and it can't be unreasonably long ago
                            $max = (new \DateTime("-18 years"))->format('Y-m-d');
                            $min = (new \DateTime("-100 years"))->format('Y-m-d');
                            return $date >= $min && $date <= $max;
                        },
                        'messages' => [
                            \Zend\Validator\Callback::INVALID_VALUE => 'date of birth has to be between 18 and 100 years ago',
                        ],
                    ],
                ],                
             ],
             'filters'=>[
                    [
                        'name' => 'StringTrim',
                    ],
                    [
                       'name' => 'Callback',
                        'options' =>[
                            'callback' => function($value){                            
                                list($M, $D, $Y) = explode('/', $value);
                                return "$Y-$M-$D";
                            },
                        ],
                    ],                    
                ],
         ];
         $spec['ssn'] = [
             'allow_empty' => true,
             'required'  => false,
              'validators' => [
                [
                    'name' => 'StringLength',
                    'options' => [
                        'min' => 9,
                        'max' => 9,
                         'messages' => [
                                \Zend\Validator\StringLength::TOO_SHORT => 'ssn must contain nine digits',
                                \Zend\Validator\StringLength::TOO_LONG => 'ssn number cannot exceed nine digits',
                        ],
                    ],
                ],
            ],
            'filters' => [
                ['name' => 'StringTrim'],
                ['name' => 'Digits', ],
            ],
         ];

         $spec['homePhone'] = [
             'allow_empty' => true,
             'required'  => false,
              'validators' => [
                    $this->phone_validator_spec,
                ],
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'Digits', ],
                ],
         ];
         /*
          * `contract_expiration_date` date DEFAULT NULL,
            `comments` varchar(600) COLLATE utf8_unicode_ci NOT NULL,
            `address1` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
            `address2` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
            `city` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
            `state` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
            `zip` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
            `country` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
          */
         // address data
         $spec['address1'] = [
             'allow_empty' => true,
             'required'  => false,
              'filters' => [
                    [ 'name' => \Zend\Filter\StringTrim::class ]
              ],
              'validators' => [
                [
                    'name' => \Zend\Validator\StringLength::class,
                    'options' => [
                        'max' => 40,
                        'messages' => [
                        \Zend\Validator\StringLength::TOO_LONG => 
                            'address exceeds maximum length of 40 characters'
                        ]
                    ]
                ]
            ]
         ];
         $spec['address2'] = [
            'allow_empty' => true,
            'required'  => false,
            'filters' => [
                [ 'name' => \Zend\Filter\StringTrim::class ]
             ],
            'validators' => [
                [
                    'name' => \Zend\Validator\StringLength::class,
                    'options' => [
                        'max' => 40,
                        'messages' => [
                            \Zend\Validator\StringLength::TOO_LONG => 
                            'address exceeds maximum length of 40 characters'
                        ]
                    ]
                ],
            ],//validators             
         ];
         $spec['city'] = [
             'allow_empty' => true,
             'required'  => false,             
         ];
         $spec['state'] = [
             'allow_empty' => true,
             'required'  => false,             
         ];
         $spec['zip'] = [
             'allow_empty' => true,
             'required'  => false,             
         ];
         $spec['country'] = [
             'allow_empty' => true,
             'required'  => false,             
         ];
         $spec['comments'] = [
             'allow_empty' => true,
             'required'  => false, 
              'filters' => [
                   [ 'name' => \Zend\Filter\StringTrim::class ]
              ],
              'validators' => [
                    [
                        'name' => \Zend\Validator\StringLength::class,
                        'options' => [
                            'max' => 600,
                            'messages' => [
                            \Zend\Validator\StringLength::TOO_LONG => 
                                'comments exceed maximumn length of 600 characters'
                            ]
                        ]
                    ]
              ]
         ];
         return $spec;
    }
}
