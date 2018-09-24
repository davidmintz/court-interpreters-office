<?php
namespace InterpretersOffice\Requests\Form;

use InterpretersOffice\Admin\Form\AbstractEventFieldset;
use InterpretersOffice\Form\ObjectManagerAwareTrait;
use InterpretersOffice\Entity;
use InterpretersOffice\Entity\EventType;
use InterpretersOffice\Entity\Repository\LocationRepository;

use Doctrine\Common\Persistence\ObjectManager;


class RequestFieldset extends AbstractEventFieldset
{

    use ObjectManagerAwareTrait;


    /**
     * name of the form.
     *
     * @var string
     */
    protected $formName = 'request-form';

    /**
     * name of this Fieldset
     * @var string
     */
    protected $fieldset_name = 'request';

    /**
     * Object manager
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * constructor
     */
    public function __construct(ObjectManager $objectManager, Array $options)
    {
        parent::__construct($objectManager, $options);
        // sanity check
        if (! key_exists('auth',$options) or ! $options['auth']
        instanceof \Zend\Authentication\AuthenticationServiceInterface ) {
            throw new \Exception(
                "constructor options to RequestFieldset must include 'auth'");
        }

        // (re)set some element attributes
        foreach (['date','time',] as $name) {
            $this->get($name)->setAttribute('placeholder', '');
        }

        $language_element = $this->get('language');
        $opts = $language_element->getValueOptions();
        $opts[0] = ['label' => '','value'=> ''];
        $language_element->setValueOptions($opts);

        $event_type_element = $this->get('eventType');
        $opts = $event_type_element->getValueOptions();
        array_unshift($opts, ['label' => ' ','value'=> '']);
        $event_type_element->setValueOptions($opts);

        $this->addDefendantsElement();
    }

    public function addDefendantsElement()
    {
        $this->add([
            'name' => 'defendants',
            'type' => 'Zend\Form\Element\Select',
            'options' => [
                'value_options' => [],
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'style' => 'display:none',
                'id' => 'defendants',
                'multiple' => 'multiple',
            ],
        ]);

    }

    /**
     * adds event-type select menu
     *
     * @return RequestFieldset
     */
    public function addEventTypeElement()
    {
        $hat = $this->options['auth']->getIdentity()->hat;
        $repo = $this->objectManager->getRepository(Entity\EventType::class);
        $options = $repo->getEventTypesForHat($hat);
        $this->add(
            [
            'type' => 'Zend\Form\Element\Select',
            'name' => 'eventType',
            'options' => [
                'label' => 'event type',
                'value_options' => $options,
            ],
            'attributes' => ['class' => 'custom-select text-muted', 'id' => 'event-type'],
            ]
        );

        return $this;
    }

    /**
     * adds location select menu
     *
     * @return RequestFieldset
     */
    public function addLocationElements()
    {
        $hat = $this->options['auth']->getIdentity()->hat;
        /** @var \InterpretersOffice\Entity\Repository\LocationRepository  $repo*/
        $repo = $this->objectManager->getRepository(Entity\Location::class);

        $options = $repo->getLocationOptionsForHat($hat);
        array_unshift($options, ['label' => ' ','value'=> '']);
        $this->add(
            [
            'type' => 'Zend\Form\Element\Select',
            'name' => 'location',
            'options' => [
                'label' => 'location',
                'value_options' => $options,
            ],
            'attributes' => ['class' => 'custom-select text-muted', 'id' => 'location'],
            ]
        );
        $this->add([
            'type' => 'textarea',
            'name' => 'comments',
            'attributes' => ['id'=>'comments', 'class'=>'form-control',
                'placeholder' => 'any noteworthy details or special instructions'
            ]
        ]);
        return $this;
    }

    public function addJudgeElements()
    {
        $user = $this->options['auth']->getIdentity();
        $repo = $this->objectManager->getRepository(Entity\Judge::class);
        $options = $repo->getJudgeOptionsForUser($user);
        array_unshift($options, ['label' => ' ','value'=> '']);
        $this->add(
            [
            'type' => 'Zend\Form\Element\Select',
            'name' => 'judge',
            'options' => [
                'label' => 'judge',
                'value_options' => $options,
            ],
            'attributes' => ['class' => 'custom-select text-muted', 'id' => 'judge'],
        ]);
        //exit("wtf");
        return $this;
    }

    public function getInputFilterSpecification()
    {
        $spec = [
            'time' => [
                'required' => true,
                'allow_empty' => false,
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages' => [
                                'isEmpty' => 'time is required'
                            ],
                        ],
                    ],
                ],
            ],
            'judge' => [
                'required' => true,
                'allow_empty' => false,
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages' => [
                                'isEmpty' => 'judge is required'
                            ],
                        ],
                    ],
                ],
            ],

            'defendants' => [
                'required' => false,
                'allow_empty' => true,
            ],

            'comments' => [
                'required' => true,
                'allow_empty' => true,
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 5,
                            'max' => 600,
                            'messages' => [
                            \Zend\Validator\StringLength::TOO_LONG =>
                                'maximum length allowed is %max% characters',
                             \Zend\Validator\StringLength::TOO_SHORT =>
                                'minimum length allowed is %min characters',
                            ]
                        ]
                    ]
                 ],
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
            ],
        ];
        return array_merge($this->inputFilterspec,$spec);
    }

}
