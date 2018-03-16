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
     * date/time properties
     *
     * @var array
     */
    protected $datetime_props =
        ['date','time','end_time','submission_date', 'submission_time'];

    /**
     * holds state of things before update
     *
     * @var array
     */
    protected $state_before = ['interpreterEvents' => [],];

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
     * implements ListenerAggregateInterface
     *
     * @param EventManagerInterface $events
     * @param integer $priority
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach('post.load', [$this, 'postLoad']);
        $this->listeners[] = $events->attach('pre.populate', [$this, 'prePopulate']);
        $this->listeners[] = $events->attach('post.validate', [$this, 'postValidate']);
        $this->listeners[] = $events->attach('pre.validate', [$this, 'preValidate']);
    }

    /**
     * Entity load event listener
     *
     * Runs after entity is fetched, but before form data is set. We save the
     * original DateTime instances for later comparison so we can avoid wasting
     * a needless update query. Also, save a snapshot of the interpreterEvents
     * so as to detect changes later.
     *
     * @param EventInterface $e
     * @return void
     */
    public function postLoad(EventInterface $e)
    {
        $controller = $e->getTarget();
        $logger = $controller->getEvent()->getApplication()
            ->getServiceManager()->get('log');
        $entity = $e->getParam('entity');
        foreach ($this->datetime_props as $prop) {
            if (strstr($prop, '_')) {
                $getter = 'get'.ucfirst(str_replace('_', '', $prop));
            } else {
                $getter = 'get'.ucfirst($prop);
            }
            $this->state_before[$prop] = $entity->$getter();
        }
        foreach ($entity->getInterpreterEvents() as $ie) {
            $this->state_before['interpreterEvents'][] = (string)$ie;
        }
        $logger->debug(sprintf('postLoad: interpreterEvents state before is now: %s',print_r($this->state_before['interpreterEvents'],true)));
    }

    /**
     * checks for updates datetime fields, interpreters
     *
     * Checks whether date/time fields actual values been modified (not just the
     * object instances), and restores them if they have not. We do this to stop
     * Doctrine from wasting an update query when no data has actually changed.
     * We also observe changes to the related InterpreterEvent entities and
     * ensure that the Event entity metadata is updated, if appropriate.
     *
     * @param EventInterface $e
     * @return void
     */
    public function postValidate(EventInterface $e)
    {
        $entity = $this->getObject();
        $controller = $e->getTarget();
        $logger = $controller->getEvent()->getApplication()->getServiceManager()->get('log');
        /** @var  Doctrine\ORM\PersistentCollection $collection */
        $collection = $entity->getInterpreterEvents();
        //*
        if ($collection->count() != count($this->state_before['interpreterEvents'])) {
            $interpreters_updated = true;
        } else {
            $after = [];
            foreach ($collection as $ie) {
                $after[] = (string)$ie;
            }
            $logger->debug(sprintf('postValidate: interpreterEvents state is now: %s',print_r($after,true)));
            $before = $this->state_before['interpreterEvents'];
            if (count($after) > 1) {
                sort($before);
                sort($after);
            }
            if ($before != $after) {
                $interpreters_updated = true;
            } else {
                $interpreters_updated = false;
            }
        }
        $logger->debug(($interpreters_updated ? "YES":"NO"). " interpreters have been changed?");

        if ($interpreters_updated) {
            // this change suffices to trigger the Event entity's preUpdate()
            $entity->setModified(new \DateTime());
        }//*/
        foreach ($this->datetime_props as $prop) {
            if (strstr($prop, '_')) {
                $getter = 'get'.ucfirst(str_replace('_', '', $prop));
            } else {
                $getter = 'get'.ucfirst($prop);
            }
            $new_value = $entity->$getter();
            $old_value = $this->state_before[$prop];
            if ($old_value == $new_value or
                (in_array($prop, ['time','end_time','submission_time'])
               && $new_value->format('g:i a') == $old_value->format('g:i a')
            )) {
                $setter = 's'.substr($getter, 1);
                $entity->$setter($old_value);
            }
        }
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
        /* there is one form control for the judge but its value may
         * correspond to either the 'judge' or the 'anonymousJudge' property,
         * and we have to make sure one is null and the other is not-null. Some
         * Javascript in the viewscript watches the judge element 'change'
         * event and sets the is_anonymouschanges in_judge flag.
         */
        if (empty($event['judge']) && empty($event['anonymousJudge'])) {
            $validator = new \Zend\Validator\NotEmpty([
                'messages' => ['isEmpty' => "judge is required"],
                'break_chain_on_failure' => true,
            ]);
            $judge_input = $this->getInputFilter()->get('event')->get('judge');
            $judge_input->setAllowEmpty(false)->setRequired(true);
            $judge_input->getValidatorChain()->attach($validator);
        } elseif ($event['is_anonymous_judge']) {
            $event['anonymousJudge'] = $event['judge'];
            unset($event['judge']);
            $entity = $this->getObject();
            if ($entity->getJudge()) {
                $entity->setJudge(null);
            }
        }
        if (! empty($event['end_time'])) {
            $end_time_input = $this->getInputFilter()->get('event')
                    ->get('end_time');
            $end_time_input->getValidatorChain()
                ->attach(new Validator\EndTimeValidator());
        }
        // heads up:  setData() has yet to happen. therefore your elements
        // like anonymousSubmitter etc will be null
        /** @todo untangle this and make error message specific to context */
        $anonSubmitterElement = $this->get('event')->get('anonymousSubmitter');
        $hat_options = $anonSubmitterElement->getValueOptions();
        $hat_id = $event['anonymousSubmitter'];
        // put the hat_id in the view -- to help persist submitter data
        // if validation fails
        $e->getTarget()->getViewModel()->hat_id = $hat_id;
        $key = array_search($hat_id, array_column($hat_options, 'value'));
        // find out if this "hat" can be anonymous with hitting the database
        $can_be_anonymous = (! $key) ? false :
                $hat_options[$key]['attributes']['data-can-be-anonymous'];

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
            $submitter_input = $this->getInputFilter()->get('event')
                ->get('submitter');
            $submitter_input->setAllowEmpty(false);
            $submitter_input->getValidatorChain()->attach($validator);
        }

        // if NO submitter but YES anonymous submitter, submitter = NULL
        if (empty($event['submitter']) && ! empty($event['anonymousSubmitter'])) {
            $event['submitter'] = null;

        // if YES submitter and YES anonymous submitter, anon submitter = NULL
        } elseif (! empty($event['submitter']) && ! empty($event['anonymousSubmitter'])) {
            $event['anonymousSubmitter'] = null;

        }
        // get defendantNames human-readable labels back into the view
        // @todo is this really necessary?
        if (isset($event['defendantNames'])) {
            $event['defendantNames'] = array_keys($event['defendantNames']);
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

        $event = $this->getObject();
        $fieldset = $this->get('event');
        // if location is set and has a parent, set parent_location element
        $location = $event->getLocation();
        if ($location && $parentLocation = $location->getParentLocation()) {
            $fieldset->get('parent_location')->setValue($parentLocation->getId());
        }
        // seems like BULLSHIT that we have to do quite so much work here.
        // what am I doing wrong that makes this necessary?

        // if submitter !== NULL, set anonymousSubmitter element = hat_id of submitter
        if (null !== $event->getSubmitter()) {
            $hat = $event->getSubmitter()->getHat();
            $fieldset->get('anonymousSubmitter')->setValue($hat->getId());
            // the form element value needs to be an integer, not an object.
            $fieldset->get('submitter')
                  ->setValue($event->getSubmitter()->getId());
        }
        $judge_element = $fieldset->get('judge');
        // judge element value needs to be an integer
        $judge = $fieldset->get('judge')->getValue();
        if (is_object($judge)) {
            $fieldset->get('judge')->setValue($judge->getId());
        }
        // if the anonymousJudge property is not null,
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
    }

    /**
     * implements InputFilterProviderInterface
     *
     * @return array
     */
    function getInputFilterSpecification()
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
    function hasTimestampMismatchError()
    {
        $errors = $this->getMessages('modified');
        return $errors &&
                key_exists(\Zend\Validator\Callback::INVALID_VALUE, $errors);
    }
}
