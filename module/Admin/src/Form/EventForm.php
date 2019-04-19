<?php
/** module/Admin/src/Form/EventForm.php */

namespace InterpretersOffice\Admin\Form;

use Zend\Form\Form as ZendForm;
use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Form\CsrfElementCreationTrait;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventInterface;

use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Filter\Word\DashToCamelCase;

use InterpretersOffice\Entity;

use InterpretersOffice\Form\DateTimeElementFilterTrait;

/**
 * form for Event entity
 *
 */
class EventForm extends ZendForm implements
    ListenerAggregateInterface,
    InputFilterProviderInterface
{

     use CsrfElementCreationTrait;
     use ListenerAggregateTrait;
     use DateTimeElementFilterTrait;

     /**
     * name of Fieldset class to instantiate and add to the form.
     *
     * subclasses can override this with the classname
     * of a Fieldset that extends EventFieldset
     *
     * @var string
     */
    protected $fieldsetClass = EventFieldset::class;

    /**
     * name of the form
     *
     * @var string
     */
    protected $formName = 'event-form';

     /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param array         $options
     */
    public function __construct(ObjectManager $objectManager, $options = null)
    {
        parent::__construct($this->formName, $options);
        $fieldset = new $this->fieldsetClass($objectManager, $options);
        $this->add($fieldset);
        /* putting this here instead of in the fieldset and handling the logic
         * ourself saves us some pain
         */
        if ("update" == $this->options['action']) {
            $this->add([
                'type' => 'Hidden',
                'name' => 'modified',
                'attributes' => ['id' => 'modified'],
            ]);
        }
        $this->addCsrfElement();
    }

    /**
     * whether Event entity was submitted through the Request module
     * @var boolean
     */
    private $is_electronic = false;

    /**
     * interpreters existing when entity was loaded
     *
     * @var array
     */
    private $interpreters_before;

    /**
     * sets is_electronic flag
     * @param boolean $bool
     */
    public function setElectronic($bool)
    {
        $this->is_electronic = $bool;
    }

    /**
     * whether event was submitted online
     *
     * @return boolean
     */
    public function isElectronic()
    {
        return $this->is_electronic;
    }

    /**
     * implements ListenerAggregateInterface
     *
     * @param EventManagerInterface $events
     * @param integer $priority
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {

        $this->listeners[] = $events->attach('pre.populate', [$this, 'prePopulate']);
        $this->listeners[] = $events->attach('pre.validate', [$this, 'preValidate']);
        $this->listeners[] = $events->attach('post.validate', [$this, 'postValidate']);
    }

   /**
    * preprocesses input and conditionally modifies validators
    *
    * @param EventInterface $e
    * @return void
    */
    public function preValidate(EventInterface $e)
    {

        $input = $e->getTarget()->getRequest()->getPost();
        $event = $input->get('event');
        $this->interpreters_before = $this->getObject()->getInterpreterEvents()
            ->toArray();

        /* there is one form control for the judge, but its value may
         * correspond to either the 'judge' or the 'anonymousJudge' property,
         * and we have to make sure one is null and the other is not-null. Some
         * Javascript (loaded by form.phtml) watches the judge element 'change'
         * event and sets the is_anonymous_judge flag.
         */
        if (empty($event['judge']) && empty($event['anonymousJudge'])) {
            $validator = new \Zend\Validator\NotEmpty([
                'messages' => ['isEmpty' => "judge is required"],
                'break_chain_on_failure' => true,
            ]);
            $judge_input = $this->getInputFilter()->get('event')->get('judge');
            $judge_input->setAllowEmpty(false)->setRequired(true);
            $judge_input->getValidatorChain()->attach($validator);
        } else {
            $entity = $this->getObject();
            if ($event['is_anonymous_judge']) {
                $event['anonymousJudge'] = $event['judge'];
                $event['judge'] = '';
                if ($entity->getJudge()) {
                    $entity->setJudge(null);
                }
            } else {
                if ($entity->getAnonymousJudge()) {
                    $entity->setAnonymousJudge(null);
                }
                $event['anonymousJudge'] = '';
            }
        }
        if (! empty($event['end_time'])) {
            $end_time_input = $this->getInputFilter()->get('event')
                    ->get('end_time');
            $end_time_input->getValidatorChain()
                ->attach(new Validator\EndTimeValidator());
        }
        // take out datetime elements that they have not changes, to
        // prevent Doctrine from wasting an update
        $this->filterDateTimeFields(
            ['date','time','end_time','submission_date','submission_time'],
            $event,
            'event'
        );

        // if the source of this Event was a Request, the metadata -- who
        // submitted it and when -- is immutable, so we're done.
        if ($this->isElectronic()) {
            $input->set('event', $event);
            return;
        }
        /*
           to solve the undefined index problem when updating just the
           interpreters from the schedule page
        */
        if ($this->getValidationGroup()) {
            return;
        }


        // heads up:  setData() has yet to happen. therefore your elements
        // like anonymousSubmitter etc will be null
        /** @todo untangle this and make error message specific to context */
        $anonSubmitterElement = $this->get('event')->get('anonymousSubmitter');
        $hat_options = $anonSubmitterElement->getValueOptions();
        $hat_id = $event['anonymousSubmitter'];
        $key = array_search($hat_id, array_column($hat_options, 'value'));
        // find out if this "hat" can be anonymous without hitting the database
        $can_be_anonymous = (! $key) ? false :
                $hat_options[$key]['attributes']['data-anonymity'] <> "0";

        $submitter_input = $this->getInputFilter()->get('event')
                ->get('submitter');

        if ((empty($event['submitter']) && empty($event['anonymousSubmitter']))
                or
            (! $can_be_anonymous  && empty($event['submitter']))
        ) {
            $validator = new \Zend\Validator\NotEmpty([
                'messages' =>
                    [ 'isEmpty' =>
                        "identity or description of submitter is required"],
                'break_chain_on_failure' => true,
            ]);
            $submitter_input->setAllowEmpty(false);
            $submitter_input->getValidatorChain()->attach($validator);
        }
        // if NO submitter but YES anonymous submitter, unset submitter
        if (empty($event['submitter']) && ! empty($event['anonymousSubmitter'])) {
            unset($event['submitter']);
            $submitter_input->setRequired(false)->setAllowEmpty(true);
        // if YES submitter and YES anonymous submitter, unset anon submitter
        } elseif (! empty($event['submitter'])
            && ! empty($event['anonymousSubmitter'])) {
            unset($event['anonymousSubmitter']);
            $anon_submitter_input = $this->getInputFilter()->get('event')
                ->get('anonymousSubmitter');
            $anon_submitter_input->setRequired(false)->setAllowEmpty(true);
        }

        $input->set('event', $event);
    }


    /**
     * processes form data before rendering
     *
     * @param EventInterface $e
     * @return void
     */
    public function prePopulate(EventInterface $e)
    {

        $fieldset = $this->get('event');
        $event = $e->getParam('entity');
        // if location is set and has a parent, set parent_location element
        $location = $event->getLocation();
        if ($location && $parentLocation = $location->getParentLocation()) {
            $fieldset->get('parent_location')->setValue($parentLocation->getId());
        }
        $judge_element = $fieldset->get('judge');
        $anonymous_judge = $event->getAnonymousJudge();
        if (is_object($anonymous_judge)) {
            // set the judge element accordingly
            $judge_element->setValue($anonymous_judge->getId());
        }

        if ($this->has('modified')) {
            $date_obj = $event->getModified();
            if ($date_obj) {
                $this->get('modified')->setValue($date_obj->format('Y-m-d H:i:s'));
            }
        }
        // if this is an update of an Event that came from a Request entity,
        // some of the metadata should be immutable
        if ($this->isElectronic()) {
            $inputFilter = $this->getInputFilter()->get('event');
            foreach (['submitter','submission_date','submission_time',
                    'anonymousSubmitter'] as $element_name) {
                $inputFilter->remove($element_name);
                $element = $fieldset->get($element_name);
                $element->setAttribute('disabled', 'disabled');
            }
        }
        // seems like BULLSHIT to have to do quite so much work here.
        // what am I doing wrong that makes this necessary?
        // if submitter !== NULL, set anonymousSubmitter element = hat_id of submitter
        if (null !== $event->getSubmitter()) {
            $hat = $event->getSubmitter()->getHat();
            $fieldset->get('anonymousSubmitter')->setValue($hat->getId());
            // the form element value needs to be an integer, not an object.
            $fieldset->get('submitter')
                  ->setValue($event->getSubmitter()->getId());
        }
    }

    /**
     * implements InputFilterProviderInterface
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        $spec = [];
        if (! $this->has('modified')) {
            return $spec;
        }

        $spec['modified'] = [
            'required' => true,
            'allow_empty' => false,
            'validators' => [
                [
                    'name' => 'NotEmpty',
                    'options' => [
                        'messages' => [
                            'isEmpty' =>
                            'form is missing last-modification timestamp',
                        ],
                    ],
                ],
                [
                    'name' => 'Date',
                    'options' => [
                        'format' => 'Y-m-d H:i:s',
                        'messages' => [
                            \Zend\Validator\Date::INVALID_DATE =>
                                'invalid modification timestamp'
                        ],
                    ]
                ]
            ],
        ];
        $em = $this->get('event')->getObjectManager();
        $spec['modified']['validators'][] = [
            'name' => 'Callback',
            'options' => [
                'callback' => function ($value, $context) use ($em) {
                    $id = $context['event']['id'];
                    $dql = 'SELECT e.modified '
                            . 'FROM InterpretersOffice\Entity\Event e '
                            . 'WHERE e.id = :id';
                    $timestamp = $em->createQuery($dql)
                            ->setParameters(['id' => $id])
                            ->getSingleScalarResult();
                    //echo "comparing $timestamp : $value";
                    return $timestamp == $value;
                },
                'messages' => [
                    \Zend\Validator\Callback::INVALID_VALUE =>
                        'Database record was modified by another process after '
                        . 'you loaded the form. In order to avoid overwriting '
                    . 'someone else\'s changes, please start over.',
                ],
            ]

        ];
        return $spec;
    }

    /**
     * is there a timestamp mismatch error?
     *
     * @return boolean
     */
    public function hasTimestampMismatchError()
    {
        $errors = $this->getMessages('modified');
        return $errors &&
            key_exists(\Zend\Validator\Callback::INVALID_VALUE, $errors);
    }

    /**
     * conditionally updates event modification timestamp, etc
     *
     * @param EventInterface $e
     * @return void
     */
    public function postValidate(EventInterface $e)
    {
        $entity = $this->getObject();
        $interpreters_posted = $entity->getInterpreterEvents()->toArray();
        if ($interpreters_posted != $this->interpreters_before) {
            $entity->setModified(new \DateTime());
            // and this suffices to make the Doctrine EventEntityListener's
            // preUpdate take note and set the last-modified-by
        }
        $fieldset = $this->get('event');
        if ($fieldset->get('anonymousSubmitter')->getValue()
            &&
            $fieldset->get('submitter')->getValue()
        ) {
            $this->getInputFilter()->get('event')
                ->remove('anonymousSubmitter');
            if ($entity->getAnonymousSubmitter()) {
                $entity->setAnonymousSubmitter(null);
            }
        }
    }
}

    /*
     * Entity load event listener DOES NOT WORK FOR SHIT.
     *
     * Runs after entity is fetched, but before form data is set. We save the
     * original DateTime instances for later comparison so we can avoid wasting
     * a needless update query. Also, save a snapshot of the interpreterEvents
     * so as to detect changes later.
     *
     * @param EventInterface $e
     * @return void
     public function __postLoad(EventInterface $e)
     {
     // not sure any of this is necessary

     // $controller = $e->getTarget();
     // $logger = $controller->getEvent()->getApplication()
     //     ->getServiceManager()->get('log');
     // $entity = $e->getParam('entity');
     // foreach ($this->datetime_props as $prop) {
     //     if (strstr($prop, '_')) {
     //         $getter = 'get'.ucfirst(str_replace('_', '', $prop));
     //     } else {
     //         $getter = 'get'.ucfirst($prop);
     //     }
     //     $this->state_before[$prop] = $entity->$getter();
     // }
     // foreach ($entity->getInterpreterEvents() as $ie) {
     //     $this->state_before['interpreterEvents'][] = (string)$ie;
     // }
     //
     // $logger->debug(sprintf(
     //     'postLoad: interpreterEvents state before is now: %s',
     //     print_r($this->state_before['interpreterEvents'], true)
     // ));
 }
     */
