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

        $this->add([
            'name' => 'courthouse',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'required' => false,
            'allow_empty' => true,
            'options' => [
                'object_manager' => $this->objectManager,
                'target_class' => 'InterpretersOffice\Entity\Location',
                'label'  => 'courthouse',
                'find_method' => ['name' => 'getCourthouses'],
                'property' => 'name',
               // 'display_empty_item' => true,
               // 'empty_item_label' => ' ',
            ],
            'attributes' => [
                'class' => 'form-control',
                'id' => 'courthouse',
            ],
        ]);

        $this->add([
            'name' => 'courtroom',
            'type' => 'Zend\Form\Element\Select',
            'required' => true,
            'allow_empty' => true,
            'options' => [
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
                'id' => 'defaultLocation'
            ],

        ]);
        //return;
        // this makes validator happy: a non-empty label
        $element = $this->get('courthouse');
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
                'label' => 'flavor',
                //'display_empty_item' => true,
                //'empty_item_label' => ' ',
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
        if (key_exists('mobilePhone', $spec)) {
            unset($spec['mobilePhone']);
        }
        return $spec;
    }
}
