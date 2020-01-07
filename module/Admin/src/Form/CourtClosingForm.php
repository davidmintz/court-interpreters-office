<?php /** module/Admin/src/Form/CourtClosingForm.php  */
namespace InterpretersOffice\Admin\Form;

use Laminas\Form\Form as LaminasForm;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\Callback;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use InterpretersOffice\Form\CsrfElementCreationTrait;
use InterpretersOffice\Service\ObjectManagerAwareTrait;
use InterpretersOffice\Entity;
use InterpretersOffice\Entity\CourtClosing;

/**
 * CourtClosingForm
 */
class CourtClosingForm extends LaminasForm implements InputFilterProviderInterface
{
    use CsrfElementCreationTrait;
    use ObjectManagerAwareTrait;

    /**
     * name of form
     * @var string
     */
    protected $formName = 'court-closing';

    /**
     * database action: update|create
     * @var string
     */
    protected $action;

    /**
     * Court Closing entity
     *
     * @var Entity\CourtClosing;
     */
    protected $entity;

    /**
    * constructor.
    *
    * @param ObjectManager $objectManager
    * @param array         $options
    */
    public function __construct(ObjectManager $objectManager, $options = null)
    {
        parent::__construct($this->formName, $options);
        if (! is_array($options)) {
            throw new \RuntimeException(
                'options required for CourtClosingForm constructor'
            );
        }
        if (! isset($options['action']) ||
            ! in_array($options['action'], ['update','create'])) {
            throw new \RuntimeException(
                '"action" constructor option must be either "update" or "create"'
            );
        }
        $this->action = $options['action'];
        $this->setObjectManager($objectManager);
        $this->setHydrator(new DoctrineHydrator($objectManager));
        $this->addHolidayElement();
        $this->add(
            [
               'type' => 'hidden',
               'name' => 'id',
               'attributes' => [
                   'id' => 'id'
               ],
            ]
        );
        $this->add([
           'type' => 'text',
           'name' => 'date',
           'attributes' => [
               'id' => 'date',
               'class' => 'form-control',
           ],
        ]);
        $this->add(
            [
               'type' => 'text',
               'name' => 'description_other',
               'attributes' => [
                   'id' => 'description_other',
                   'class' => 'form-control',
                   'placeholder' => 'brief explanation of reason for closing'
               ],
            ]
        );
        $this->addCsrfElement('court_closing_csrf');
    }
   /**
    * adds the select element for standard holidays
    */
    protected function addHolidayElement()
    {
        $value_options = $this->objectManager
        ->getRepository(Entity\CourtClosing::class)
        ->getHolidays();
        array_push($value_options, ['label' => 'other...','value' => 'other']);
        array_unshift($value_options, ['label' => ' ','value' => '']);
        $this->add([
           'type' => 'Select',
           'name' => 'holiday',
           'options' => [
               'label' => 'holiday',
               'value_options' => $value_options,
               'disable_inarray_validator' => true,
           ],
           'attributes' => [
               'class' => 'form-control custom-select',
               'id' => 'holiday',
           ],
        ]);
    }

   /**
    * implements InputFilterProviderInterface
    *
    * @return Array
    */
    public function getInputFilterSpecification()
    {

        $repository = $this->objectManager->getRepository(Entity\CourtClosing::class);
        $action = $this->action;

        $uniqueness_validator = [
            'name' => Callback::class,
            'options' => [
                'callback' => function ($value, $context) use ($repository, $action) {
                    $entity = $repository->findOneBy(['date' => new \DateTime($value)]);
                    if ($action == 'create' && $entity) {
                        return false;
                    }
                    if ($action == 'update') {
                        if ($entity && $entity->getId() != $context['id']) {
                            return false;
                        }
                    }
                    return true;
                } ,
                'messages' => [
                   Callback::INVALID_VALUE =>
                     'There is already a closing for this date in your database',
                ],
            ],
        ];
        return [
            'id' => [
                'required' => true,
                'allow_empty' => $this->action == 'create',
            ],
            'date' => [
                'required' => true,
                'allow_empty' => false,
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages' => [
                                 'isEmpty' => 'date is required',
                             ],
                        ],
                    ],
                    $uniqueness_validator,
                ],
            ],
            'holiday' => [
                'required' => true,
                'allow_empty' => false,
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages' => [
                                 'isEmpty' => 'holiday is required',
                             ],
                        ],
                        'break_chain_on_failure' => true,
                    ],
                    [
                        'name' => Callback::class,
                        'options' => [
                            'callback' => function ($value, $context) {
                                // one of these has to be truthy
                                return (int)$value
                                 or $context['description_other'];
                            } ,
                            'messages' => [
                               Callback::INVALID_VALUE =>
                                 'either a holiday or alternative description is required',
                            ],
                        ],
                    ],
                ],
                'filters' => [
                    ['name' => 'ToNull'],
                ],
            ],
            'description_other' => [
                'required' => true,
                'allow_empty' => true,
                'validators' => [
                   [
                       'name' => 'StringLength',
                       'options' => [
                           'min' => 4,
                           'max' => 75,
                           'messages' => [
                           \Laminas\Validator\StringLength::TOO_LONG =>
                               'maximum length allowed is %max% characters',
                            \Laminas\Validator\StringLength::TOO_SHORT =>
                               'minimum length allowed is %min% characters',
                           ],
                       ],
                   ],
                ],
                'filters' => [
                   ['name' => 'StringTrim'],
                   ['name' => 'ToNull'],
                ],
            ],
        ];
    }
}
