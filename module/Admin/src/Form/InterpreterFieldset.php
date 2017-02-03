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
     * constructor
     * 
     */
    public function __construct(ObjectManager $objectManager, $options = [])
    {
    	parent::__construct($objectManager, $options);
        /*
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
        
        $this->add([
    		'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'language-select',
            'options' => [
                'object_manager' => $this->objectManager,
                'target_class' => 'InterpretersOffice\Entity\Language',
                'property' => 'name',
                //'find_method' => ['name' => 'getInterpreterHats'],
                'label' => 'languages',
                'option_attributes' =>
                   [ 'data-certifiable' => function(Entity\Language $language){
                        return $language->isFederallyCertified() ? 1 : 0;
                   },]
            ],
             'attributes' => [
                'class' => 'form-control',
                'id' => 'language-select',
             ],
        ]);
        $element = $this->get('language-select');
        $options = $element->getValueOptions();
        array_unshift($options, [
            'label' => '-- select a language --',
            // keeps the allow_empty => false option from preventing
            // the callback validator from running:
            'value' => '', 
            'attributes' => ['label' => ' ', ],
        ]);
        $element->setValueOptions($options);  
        
        $this->add([
            'type'=> 'Select',
            'name' => 'interpreter-languages',
            
            
            'attributes' => [
                'multiple' => 'multiple', 
                'class' => 'hidden',
            ],
            'options' => [
                'value_options' => [],
                'disable_inarray_validator' => true,
                'use_hidden_element' => true,
            ]
        ]);

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
    
    public function getInputFilterSpecification() {
      
        $spec = parent::getInputFilterSpecification();

        $language_options = $this->get('language-select')->getValueOptions();
        $certifiable = array_column($language_options, 'attributes', 'value');
        //echo "<pre>";    
        //print_r($certifiable);
        //echo "</pre>";        
        $spec['interpreter-languages'] = [

            'allow_empty' => false,
            'required'  => true,
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
                    // is set, if appropriate
                    'name' => 'Callback',                  
                    'options' => [
                        'callback'=> function($value,$context) use ($certifiable) {
                           $languages_submitted = $context['interpreter-languages'];                           
                           foreach ($languages_submitted as $language) {
                               $id = $language['language_id'];
                               // should not happen unless they are messing with us
                               if (! isset($language['federalCertification'])) {
                                   return false;
                               }
                               $submitted_cert = is_numeric($language['federalCertification']) ?
                                       (boolean)$language['federalCertification'] : null;
                               $cert_required = (boolean)$certifiable[$id]['data-certifiable'];
                               if ($cert_required && !is_bool($submitted_cert)) {
                                   return false;
                               }                               
                               //echo "id: $id\n"; echo "value submitted: ";var_dump($submitted_cert);
                               //echo "bool value required? ";var_dump($cert_required);                               
                           }
                           return true;
                        },
                        'messages' => 
                        [
                            \Zend\Validator\Callback::INVALID_VALUE =>
                                'yes/no required for federal certification'                            
                        ],
                    ],                    
                ],
            ],
        ];

        // this one is just for the UI
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
     
        return $spec;
         
    }
}