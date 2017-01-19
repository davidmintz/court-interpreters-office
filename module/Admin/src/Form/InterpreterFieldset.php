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
            'value' => '-1', 
            'attributes' => ['label' => ' ', ],
        ]);
        $element->setValueOptions($options);    

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
        /* // does not seem to work for validating this shit
        $spec['interpreterLanguages'] = [
            'required' => true,
            'allow_empty' => false,
            'validators' => 
            [
                [
                    'name' => 'NotEmpty',
                    'options' => [
                        'messages' => [
                            'isEmpty' => 'at least one language is required',
                        ],
                    ],
                    'break_chain_on_failure' => true,
                ],
            ],
            
        ];
        */
        // this one is just for the UI
         $spec['language-select'] = [
            'required' => true,
            'allow_empty' => false,
            'validators' => [

                [   // attach this validator to the language-select element to ensure 
                    // the interpreter-languages collection is not empty
                    // cheating, maybe, but it works whereas other attempts have failed 
                   'name'=> 'Zend\Validator\Callback',
                   'options' => [
                        'callback' => function($value){
                            $shit = $this->getObject()->getInterpreterLanguages();
                            return $shit->count() ?: false;                         
                        },
                        'messages' => [
                            'callbackValue' => 'At least one language is required',
                        ],
                    ],
                ],
            ],

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