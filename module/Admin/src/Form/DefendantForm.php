<?php
/** module/Admin/src/Form/EventForm.php */

namespace InterpretersOffice\Admin\Form;

use Zend\Form\Form as ZendForm;
use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Form\CsrfElementCreationTrait;

//use Zend\EventManager\ListenerAggregateInterface;
//use Zend\EventManager\ListenerAggregateTrait;
//use Zend\EventManager\EventManagerInterface;
//use Zend\EventManager\EventInterface;

use Zend\InputFilter\InputFilterProviderInterface;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use InterpretersOffice\Form\ObjectManagerAwareTrait;

/**
 * form for Event entity
 *
 */
class DefendantForm extends ZendForm implements InputFilterProviderInterface
{

     use CsrfElementCreationTrait;

     use ObjectManagerAwareTrait;

     const USE_EXISTING = 'use_existing';
     const UPDATE_EXISTING = 'update_existing';

    /**
     * name of the form
     *
     * @var string
     */
    protected $formName = 'defendant-form';


     /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param array         $options
     */
    public function __construct(ObjectManager $objectManager, $options = null)
    {
        parent::__construct($this->formName, $options);
        $this->setObjectManager($objectManager);
        $this->setHydrator(new DoctrineHydrator($objectManager, true));

        $this->addCsrfElement('defendant_csrf');
        $this->add(
            [
            'type' => 'Zend\Form\Element\Text',
            'name' => 'surnames',
            'options' => [
                'label' => 'surname(s)',
            ],
            'attributes' => [
                'class' => 'form-control',
                'id' => 'surnames',
            ],
             ]
        );
        $this->add(
            [
            'type' => 'Zend\Form\Element\Text',
            'name' => 'given_names',
            'options' => [
                'label' => 'given name(s)',
            ],
            'attributes' => [
                'class' => 'form-control',
                'id' => 'given_names',
            ],
             ]
        );

        $this->add([
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'id',
            'required' => true,
            'allow_empty' => true,
        ]);


        /* TO BE CONTINUED: if $option['action'] == update, more elements... */
        //if ($options['action'] == 'update') {
            $this->add([
                'type' => 'Zend\Form\Element\Select',
                'name' => 'occurrences',
                'attributes' => ['multiple' => 'multiple'],

            ]);
            $this->add([
                'type' => 'Zend\Form\Element\Radio',
                'name' => 'duplicate_resolution',
                'attributes' => ['id' => 'duplicate_resolution'],
                'options' => [
                    'value_options' => [
                        self::USE_EXISTING => 'use the existing version as is',
                        self::UPDATE_EXISTING => 'update the existing version (as shown below)',
                    ],
                ]
            ]);
        //}
    }

    /**
     * implements InputFilterProviderInterface
     *
     * @return array
     *
     * @todo uniqueness validator. see PersonFieldset for an example
     */
    function getInputFilterSpecification()
    {
        $spec = [
            'surnames' => [
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages' => [
                                'isEmpty' => 'surname is required',
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
            'given_names' => [
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages' => [
                                'isEmpty' => 'given name is required',
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
            'occurrences' => [
                'required' => false,
                'allow_empty' => true,
            ],
            'duplicate_resolution' => [
                'required' => false,
                'allow_empty' => true,
            ],
        ];
        return $spec;
    }

    /**
     * attaches validator for "occurrences" of a given name/docket
     *
     * @return DefendantForm
     */
    public function attachOccurencesValidator()
    {
        $input = $this->getInputFilter()->get('occurrences')
            ->setRequired(true)->setAllowEmpty(false);
        $validator = new \Zend\Validator\NotEmpty([
            'messages' => ['isEmpty' => "at least one of the above must be selected"],
            'break_chain_on_failure' => true,
        ]);
        $input->getValidatorChain()->attach($validator);

        return $this;
    }

    /**
     * attaches validator for the duplicate resolution policy
     *
     * @return DefendantForm
     */
    public function attachDuplicateResolutionValidator()
    {
        $input = $this->getInputFilter()->get('duplicate_resolution')
            ->setRequired(true)->setAllowEmpty(false);
        $validator = new \Zend\Validator\NotEmpty([
            'messages' => ['isEmpty' => "at least one of the above must be selected"],
            'break_chain_on_failure' => true,
        ]);
        $input->getValidatorChain()->attach($validator);

        return $this;
    }
}
