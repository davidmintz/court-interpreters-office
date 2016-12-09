<?php

/** module/InterpretersOffice/src/Form/PersonFieldset.php */

namespace InterpretersOffice\Form;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

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
    ];
    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param array         $options
     */
    public function __construct(ObjectManager $objectManager, $options = [])
    {
        parent::__construct('person-fieldset', $options);
        $this->objectManager = $objectManager;
        foreach ($this->elements as $element) {
            $this->add($element);
        }
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

        ];
    }
}
