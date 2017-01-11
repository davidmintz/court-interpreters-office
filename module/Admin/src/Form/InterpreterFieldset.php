<?php
/**
 * module/Admin/src/Form/InterpreterFieldset.php.
 */

namespace InterpretersOffice\Admin\Form;

use InterpretersOffice\Form\PersonFieldset;
use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Entity\Interpreter;

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
    		'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'language-select',
            'options' => [
                'object_manager' => $this->objectManager,
                'target_class' => 'InterpretersOffice\Entity\Language',
                'property' => 'name',
                //'find_method' => ['name' => 'getInterpreterHats'],
                'label' => 'languages',
            ],
             'attributes' => [
                'class' => 'form-control',
                'id' => 'language-select',
             ],
        ]);
        $element = $this->get('language-select');
        $options = $element->getValueOptions();
        array_unshift($options, [
           'label' => ' ',
           'value' => '',
           'attributes' => [
               'label' => ' ',

           ],
        ]);
        $element->setValueOptions($options);
        $more_options = [];
        foreach ($options as $opt) {
            $more_options[$opt['value']] = $opt['label'];
        }
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'interpreterLanguages',
                'options' => [
                    'multiple'=> true,
                    'value_options' => $more_options,
                 ],
                'attributes' => ['multiple'=>'multiple',],
            ]
        );
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
        // this one is just for the UI
         $spec['language-select'] = [
            'required' => false,
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