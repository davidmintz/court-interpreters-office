<?php
/**
 * module/Admin/src/Form/JudgeFieldset.php
 */

namespace InterpretersOffice\Admin\Form;

use InterpretersOffice\Form\PersonFieldset;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * JudgeFieldset
 *
 * @author david
 */
class JudgeFieldset extends PersonFieldset {
    
    /**
     * name of the fieldset.
     * 
     * since we are a subclass of PersonFieldset, this needs to be overriden
     * 
     * @var string
     */
    protected $fieldset_name = 'judge';
    
    public function __construct(ObjectManager $objectManager, $options = array())
    {
        parent::__construct($objectManager, $options);
        $this->add([
            'name' => 'defaultLocation',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'required' => true,
            'allow_empty' => true,
            'options' => [
                'object_manager' => $this->objectManager,
                'target_class' => 'InterpretersOffice\Entity\Location',
                'label' => 'default location',
                'property' => 'name',
                //'display_empty_item' => true,
                //'empty_item_label' => ' ',
                'find_method' => ['name' => 'getJudgeLocations'],
                 'label_generator' => function ($location) {
                    if ($parent = $location->getParentLocation()) {
                        return sprintf('courtroom %s, %s',
                        $location->getName(),
                        $parent->getName());
                    }
                    return $location->getName();
                },
                
            ],
            'attributes' => [
                'class' => 'form-control',
                'id' => 'defaultLocation',
            ], 
        ]);
        //return;
        // this makes validator happy: a non-empty label
        $element = $this->get('defaultLocation');
        $options = $element->getValueOptions();
        array_unshift($options, [
           'label' => ' ',
           'value' => '',
           'attributes' => [
               'label' => ' ',
           ],
       ]);
       $element->setValueOptions($options);
    }
    
    public function addHatElement() {
        
        // there might be a better way.
        $this->add(
            [
                'name' => 'hat',
                'type' => 'Zend\Form\Element\Hidden',
            ]
        );
        $this->add([
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'flavor',
            'required' => true,
            'allow_empty' => false,
            'options' => [
                'object_manager' => $this->objectManager,
                'target_class' => 'InterpretersOffice\Entity\JudgeFlavor',
                'property' => 'flavor',
                //'label' => 'flavor',
                //'display_empty_item' => true,
                'empty_item_label' => ' ',
            ],
             'attributes' => [
                'class' => 'form-control',
                'id' => 'flavor',
             ],              
       ]);
        //return;
       $element = $this->get('flavor');
       $options = $element->getValueOptions();
       array_unshift($options, [
           'label' => ' ',
           'value' => '',
           'attributes' => [
               'label' => ' ',
           ],
       ]);
       $element->setValueOptions($options);
    }
    
    public function getInputFilterSpecification() {
        $spec = parent::getInputFilterSpecification();
        $spec['flavor'] = [
            'required' => true,
            'validators' => [
                [
                    'name' => 'NotEmpty',
                    'options' => [
                        'messages' => [
                            'isEmpty' => 'judge "flavor" is required',
                        ],
                    ],
                ],
            ],
        ];

        $spec['defaultLocation'] = [
            'required' => false,
            'allow_empty' => true,
        ];
        if (key_exists('mobilePhone',$spec)) {
            unset($spec['mobilePhone']);
        }
        return $spec;
    }
}
