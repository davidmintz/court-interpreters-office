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
class JudgeFieldset extends PersonFieldset
{

    /**
     * name of the fieldset.
     *
     * since we are a subclass of PersonFieldset, this needs to be overriden
     *
     * @var string
     */
    protected $fieldset_name = 'judge';

    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param array $options
     */
    public function __construct(ObjectManager $objectManager, $options = [])
    {
        parent::__construct($objectManager, $options);
        if (isset($options['object'])) { 
            if (! $options['object'] instanceof \InterpretersOffice\Entity\Judge) {
                throw new \InvalidArgumentException("object has to be an instance of Judge...");
            }
            $judge = $options['object'];

            if ($defaultLocation = $judge->getDefaultLocation()) {
                if ('courtroom' == $defaultLocation->getType()) {
                    $location_id = $defaultLocation->getId();
                    $parent_id = $defaultLocation->getParentLocation()->getId();
                } else {
                    $parent_id = $defaultLocation->getId();
                    $location_id = $parent_id;
                }
            }
             
        } else {
            // maybe we don't need to do this, if getCourtrooms() were 
            // to accept an optional parent_id
            $judge = null;
            $parent_id = 0; 
        }
        $this->add([
            'name' => 'courthouse',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'options' => [
                'object_manager' => $this->objectManager,
                'target_class' => 'InterpretersOffice\Entity\Location',
                'label'  => 'courthouse',
                'find_method' => ['name' => 'getCourthouses'],
                'property' => 'name',
            ],
            'attributes' => [
                'class' => 'form-control',
                'id' => 'courthouse',
            ],
        ]);
        $this->add([
            'name' => 'courtroom',
            'type' =>  'DoctrineModule\Form\Element\ObjectSelect',
            'options' => [
                'object_manager' => $this->objectManager,
                'target_class' => 'InterpretersOffice\Entity\Location',
                'label'  => 'courtroom',
                'find_method' => [
                    'name' => 'getCourtrooms',
                    'params' => ['parent_id' => $parent_id]
                 ],
                'property' => 'name',
            ],
            'attributes' => [
                'class' => 'form-control',
                'id' => 'courtroom',
            ],
        ]);
        $this->add([
            
            'name' => 'defaultLocation',
            'type' => 'Zend\Form\Element\Hidden',
            'required' => true,
            'allow_empty' => true,
            'attributes' => [
                'id' => 'defaultLocation',
                
            ],

        ]);
        // return;
        // this makes validator happy: a non-empty label
        foreach (['courthouse','courtroom'] as $elementName) {
            $element = $this->get($elementName);
            $valueOptions = $element->getValueOptions();
            array_unshift($valueOptions, [
               'label' => ' ',
               'value' => '',
               'attributes' => [
                   'label' => ' ',
               ],
            ]);
            $element->setValueOptions($valueOptions);
        }
        $this->get('courthouse')->setValue($parent_id);

        if ($options['action'] == 'update' && $location_id != $parent_id) {
            $this->get('courtroom')->setValue($location_id);
        }
        
       
    }

    /**
     * adds the "Hat" element to our form
     *
     * @return JudgeFieldset
     *
     */
    public function addHatElement()
    {

        // there might be a better way.
        $this->add(
            [
                'name' => 'hat',
                'type' => 'Zend\Form\Element\Hidden',
                'attributes' => [ 'id' => 'hat'],
            ]
        );
        $this->add([
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'flavor',
            'options' => [
                'object_manager' => $this->objectManager,
                'target_class' => 'InterpretersOffice\Entity\JudgeFlavor',
                'property' => 'flavor',
                'label' => 'flavor',
            ],
             'attributes' => [
                'class' => 'form-control',
                'id' => 'flavor',
             ],
        ]);
        // hack designed to please HTML5 validator
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

        return $this;
    }


    /**
     * gets input filter specification
     *
     * @return array
     *
     */
    public function getInputFilterSpecification()
    {

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
        $spec['courthouse'] = [
            'required' => false,
            'allow_empty' => true,
        ];
        $spec['courtroom'] = [
            'required' => false,
            'allow_empty' => true,
        ];
        if (key_exists('mobilePhone', $spec)) {
            unset($spec['mobilePhone']);
        }
        return $spec;
    }
}
