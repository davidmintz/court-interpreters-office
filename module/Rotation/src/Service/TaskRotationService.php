<?php /** module/Rotation/src/Service/TaskRotationService.php */
declare(strict_types=1);

namespace InterpretersOffice\Admin\Rotation\Service;

use Doctrine\ORM\EntityManagerInterface;
use DateTime;
use InterpretersOffice\Admin\Rotation\Entity;
use InterpretersOffice\Entity\Person;
use InterpretersOffice\Admin\Rotation\Entity\RotationRepository;
use Zend\EventManager\EventInterface;
use Zend\View\Model\JsonModel;

use Zend\InputFilter;
use Zend\Validator;
use Zend\Filter;

/**
 * TaskRotationService
 */
class TaskRotationService
{
    /**
     * entity manager
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * configuration options
     *
     * @var array
     */
    private $config;

    /**
     * rotation inputfilter
     *
     * @var InputFilter
     */
    private $rotationInputFilter;

    /**
     * substitution inputfilter
     *
     * @var InputFilter
     */
    private $substitutionInputFilter;

    /**
     * repository
     *
     * @var Entity\RotationRepository
     */
    private $repo;

    public function __construct(EntityManagerInterface $em, Array $config)
    {
        $this->em = $em;
        $this->config = $config;
    }

    /**
     * returns input filter for Rotation entity
     *
     * @return InputFilter\InputFilter
     */
    public function getRotationInputFilter() : InputFilter\InputFilter
    {
        if ($this->rotationInputFilter) {
            return $this->rotationInputFilter;
        }
        $inputFilter = new InputFilter\InputFilter();
        $em = $this->em;
        $inputFilter->add(
            [
                'name' => 'members',
                'type' => InputFilter\ArrayInput::class,
                'required' => true,
                'validators' =>[
                    [
                        'name' => 'Digits',
                        'options' => [
                            'messages' => [
                                'notDigits' =>
                                '"members" input must be only digits, got: %value%'
                            ],
                        ],
                    ],
                    [
                        'name' => 'Callback',
                        'options' => [
                            'callBack' => function($value, $context) use ($em) {
                                $person = $em->find('InterpretersOffice\Entity\Person',$value);
                                return $person && $person->getActive() ?: false;
                            },
                            'messages' => [
                                'callbackValue' => '%value% is not the id of an active person',
                            ],
                        ],
                    ],

                ],
                'filters' =>[
                    [
                        'name' => 'StringTrim'
                    ],
                ],
            ]
        );
        $inputFilter->add([
            'name' => 'countable',
            'required' => true,
            'validators' => [
                [
                    'name' => Validator\IsCountable::class,
                    'options' => [
                        'min' => 2,
                        'messages' => [
                            'lessThan' => "a minimum %min% persons required",
                            'notCountable' => '"countable" has to be a countable data type',
                        ],
                    ],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name' => 'Callback',
                    'options' => [
                        'callBack' => function($value) {
                            $unique = \array_unique($value);
                            return count($value) == count($unique);
                        },
                        'messages' => [
                            'callbackValue' => 'each person in the rotation must be unique (no duplicates)',
                        ],
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name' => 'start_date',
            'validators' => [
                [
                    'name' => 'NotEmpty',
                    'options' => [
                        'messages' => [
                            'isEmpty' => 'start date is required'
                        ],
                    ],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name' => 'Date',
                    'options' => [
                        'messages' => [
                            'dateInvalidDate' => '"%value%" is not a valid date'
                        ],
                    ],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name' => 'Callback',
                    'options' => [
                        'callBack' => function($value, $context) use ($em) {
                            $task_id = $context['task'] ?? null;
                            if (! $task_id) {
                                return true; // another validator will handle it
                            }
                            $task = $em->find(Entity\Task::class,$context['task']);
                            if (! $task) { return true; } // same as above
                            // what is the task duration?
                            $frequency = $task->getFrequency();
                            $date = new \DateTime($value);
                            $dow = $date->format('N');
                            if  ('WEEK' == $frequency && $dow != 1) {
                                return false;
                            }
                            return true;
                        },
                        'messages' => [
                            'callbackValue' => 'start date for weekly task rotations should be a Monday',
                        ],
                    ],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name' => 'Callback',
                    'options' => [
                        'callBack' => function($date, $context) use ($em) {
                            if (! isset($context['task'])) { return true; }
                            $task = $em->find(Entity\Task::class,$context['task']);
                            $today = new \DateTime();
                            return  $date >= $today;

                        },
                        'messages' => [
                            'callbackValue' => 'retroactive creation or modification of tasks and rotations is not supported',
                        ],
                    ],
                ],
            ],
        ]);
        $this->rotationInputFilter = $inputFilter;

        return $inputFilter;
    }

    /**
     * gets input filter for Substitution entity
     *
     * @return InputFilter\InputFilter
     */
    public function getSubstitutionInputFilter() : InputFilter\InputFilter
    {
        if ($this->substitutionInputFilter) {
            return $this->substitutionInputFilter;
        }
        $inputFilter = new InputFilter\InputFilter();
        $em = $this->em;
        $inputFilter->add([
            'name' => 'csrf',
            'validators' => [
                [
                    'name' => 'NotEmpty',
                    'options' => [
                        'messages' => [
                            'isEmpty' => 'required security token is missing'
                        ],
                    ],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name' => 'Csrf',
                    'options' => [
                        'messages' => [
                            'notSame' =>
                            'Invalid or expired security token. Please reload this page and try again.'
                    ]],
                ],
            ],
        ]);

        $inputFilter->add([
            'name' => 'date',
            'validators' => [
                [
                    'name' => 'NotEmpty',
                    'options' => [
                        'messages' => ['isEmpty'=> "date is required"],
                    ],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name' => 'Date',
                    'options' => [
                        'messages' => ['dateInvalidDate'=>'invalid/malformed date: "%value%"'],
                    ],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name' => 'Callback',
                    'options' => [
                        'callBack' => function($value, $context) use ($em) {
                            $rotation_id = $context['rotation_id'] ?? null;
                            if (! $rotation_id) {
                                return true; // to be handled elsewhere
                            }
                            $rotation = $em->find(Entity\Rotation::class, $rotation_id);
                            if (! $rotation_id) {
                                return true; // same as as above
                            }

                            return $value >=  $rotation->getStartDate()->format('Y-m-d');
                        },
                        'messages' => [
                            'callbackValue' => 'submitted date predates the start date of the rotation',
                        ],
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name' => 'task',
            'validators' => [
                [
                    'name' => 'NotEmpty',
                    'options' => [
                        'messages' => ['isEmpty'=> "task entity id is required"],
                    ],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name' => 'Digits',
                    'options' => ['messages' => [
                        'notDigits' => 'invalid valid task entity id: "%value%"'
                    ]],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name' => 'Callback',
                    'options' => [
                        'callBack' => function($value, $context) {
                            $task = $this->getTask((int)$value);
                            if (! $task) {
                                return false;
                            }
                            return true;
                        },
                        'messages' => [
                            'callbackValue' => '%value% is not the id of any task',
                        ],
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name' => 'person',
            'validators' => [
                [
                    'name' => 'NotEmpty',
                    'options' => [
                        'messages' => ['isEmpty'=> "person entity id is required"],
                    ],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name' => 'Digits',
                    'options' => ['messages' => [
                        'notDigits' => 'invalid person entity id: "%value%"'
                    ]],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name' => 'Callback',
                    'options' => [
                        'callBack' => function($value, $context) use ($em) {
                            $person = $em->find('InterpretersOffice\Entity\Person',$value);
                            return $person && $person->isActive() ? true : false;
                        },
                        'messages' => [
                            'callbackValue' => '%value% is not the id of any active person',
                        ],
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name' => 'duration',
            'required' => true,
            'validators' => [
                [
                    'name' => 'NotEmpty',
                    'options' => [
                        'messages' => ['isEmpty'=> '"duration" field is required'],
                    ],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name' => 'InArray',
                    'options' => [
                        'haystack' => [ "DAY", "WEEK"],
                        'messages' => [
                            'notInArray' => 'the only supported options for duration are "DAY" and "WEEK"'
                        ],
                    ],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name' => 'Callback',
                    'options' => [
                        'callBack' => function($value, $context) {
                            if ($value == 'DAY') {
                                return true; // shouldn't be a problem
                            }
                            if (! isset($context['task'])) {
                                return true; // someone else's problem
                            }
                            $task = $this->getTask((int)$context['task']);
                            if (! $task) {
                                return true; // ditto; another validator will handle it
                            }
                            return $task->getDuration() != 'DAY';

                        },
                        'messages' => [
                            'callbackValue' => 'the duration for this substitution is inconsistent with the duration of the task',
                        ],
                    ],
                ],
            ],
            'filters' => [
                [
                    'name' => 'StringToUpper'
                ]
            ],
        ]);
        $inputFilter->add([
            'name' => 'substitution',
            'required' => true,
            'allow_empty' => true,

            'validators' => [
                // [
                //     'name' => 'NotEmpty',
                //     'options' => [
                //         'messages' => ['isEmpty'=> "substitution is required"],
                //     ],
                //     'break_chain_on_failure' => true,
                // ],
                [
                    'name' => 'Callback',
                    'options' => [
                        'callBack' => function($value, $context) use ($em) {
                            $substitution = $em->find(Entity\Substitution::class,$value);
                            return $substitution ? true : false;
                        },
                        'messages' => [
                            'callbackValue' => 'no substitution with id %value% was found.',
                        ],
                    ],
                ],
            ],
        ]);

        $inputFilter->add([
            'name' => 'rotation_id',
            'required' => true,
            'allow_empty' => false,
            'validators' => [
                [
                    'name' => 'NotEmpty',
                    'options' => [
                        'messages' => ['isEmpty'=> "rotation entity id is required"],
                    ],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name' => 'Digits',
                    'options' => ['messages' => [
                        'notDigits' => 'invalid rotation entity id: "%value%"'
                    ]],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name' => 'Callback',
                    'options' => [
                        'callBack' => function($value, $context) use ($em) {
                            $rotation = $em->find(Entity\Rotation::class,$value);
                            return $rotation ? true : false;
                        },
                        'messages' => [
                            'callbackValue' => 'no task rotation with id %value% was found.',
                        ],
                        'break_chain_on_failure' => true,
                    ],
                ],
            ],
        ]);


        $this->substitutionInputFilter = $inputFilter;

        return $inputFilter;
    }

    /**
     * gets assignments for a date
     *
     * The $note_types provide configuration keys that tell us the id of the
     * Task for which to fetch the assignment for $date
     *
     * @param array $types, e.g., ['motd','motw']
     * @param \DateTime $date
     * @return Array
     */
    public function getAssignmentsForNotes(Array $note_types, DateTime $date) : Array
    {
        $display_config = $this->config['display_rotating_assignments'] ;
        $result = [];
        /** @var $repo InterpretersOffice\Admin\Rotation\Entity\RotationRepository */
        $repo = $this->em->getRepository(Entity\Rotation::class);
        foreach ($note_types as $type) {
            if (empty($display_config[$type])) {
                continue;
            }
            $result[$type] = [];
            foreach($display_config[$type] as $task_id) {
                $task = $repo->getTask($task_id);
                /** @todo if this is going to be like this, log a warning */
                if ($task) {
                    $assignment = $repo->getAssignment($task, $date);
                    $result[$type][$task->getName()] = $assignment;
                }
            }
        }

        return $result;
    }

    /**
     * gets assignment for task $task_id on $date
     *
     * @param  string $date
     * @param  int    $task_id
     * @throws \InvalidArgumentException
     * @return Array
     */
    public function getAssignment(string $date, int $task_id) : Array
    {
        try {
            $date_obj = new DateTime($date);
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException("invalid date parameter: {$e->getMessage()}");
        }
        $task = $this->getTask($task_id);
        if (! $task) {
            throw new \InvalidArgumentException("no task entity with id $task_id was found",404);
        }

        $result = $this->getRepository()->getAssignment($task,$date_obj);

        return $this->assignmentToArray($result);
    }

    /**
     * lazy-gets repository
     *
     * @return Entity\RotationRepository
     */
    public function getRepository()
    {
        if ($this->repo) {
            return $this->repo;
        }
        $this->repo = $this->em->getRepository(Entity\Rotation::class);

        return $this->repo;
    }

    /**
     * proxies to  Entity\RotationRepository::getTask()
     *
     * note to self: maybe remove the repo method and do the work here and
     * update all the client code...
     *
     * @param  int    $id
     * @return Entity\Task|null
     */
    public function getTask(int $id) : ? Entity\Task
    {
        return $repo = $this->getRepository()->getTask($id);
    }



    /**
     * Conditionally injects Rotation data into view.
     *
     * Listener for NOTES_RENDER (MOT[DW]) inject s Rotation (Task)
     * data into the view.
     *
     * @param  EventInterface $event
     * @return void
     */
    public function initializeView(EventInterface $e)
    {
        $mvcEvent = $e->getParam('event');
        $date = $e->getParam('date');
        $container =  $mvcEvent->getApplication()->getServiceManager();
        $log = $container->get('log');
        $log->debug("heeeeeeeeere's Johnny in ".__METHOD__ . " where shit was triggered");
        $rotation_config = $container->get('config')['rotation'] ?? null;
        if (! $rotation_config or !isset($rotation_config['display_rotating_assignments'])) {
            $log->debug("no task-rotation config, returning");
            return;
        }
        $note_types = $e->getParam('note_types',[]);
        if (! $note_types) {
            $settings = $e->getParam('settings');
            foreach (['motd','motw'] as $type) {
                if ($settings[$type]['visible']) {
                    $note_types[] = $type;
                }
            }
        }

        //$service = $container->get(Service\TaskRotationService::class);
        $assignment_notes = $this->getAssignmentsForNotes($note_types,$date);
        if ($assignment_notes) {
            $log->debug(count($assignment_notes) . ' assignment notes found');
            $view = $e->getParam('view') ?:
                $mvcEvent->getApplication()->getMvcEvent()->getViewModel();
            if ($view instanceof JsonModel) {
                $log->debug("HA! need to inject JSON data");
                $view->assignment_notes = $this->assignmentNotesToArray($assignment_notes);
            } else {
                $log->debug("NOT a JsonModel?");
                $view->assignment_notes = $assignment_notes;
            }
        }
    }

    /**
     * helper to return JSON-friendlier representation
     *
     * @param  Array $assignment_notes
     * @return Array
     */
    public function assignmentNotesToArray(Array $assignment_notes): Array
    {
        $return = [];
        foreach ($assignment_notes as $note_type => $data) {
            $return[$note_type] = [];
            foreach($data as $task => $people) {
                $return[$note_type][$task] = [
                    // 'assigned' => $people['assigned']->getFirstName(),
                    // 'default' => $people['default']->getFirstName(),
                    'assigned' => [
                        'name' => $people['assigned']->getFirstName(),
                        'id'   => $people['assigned']->getId(),
                    ],
                    'default' => [
                        'name' => $people['assigned']->getFirstName(),
                        'id'   => $people['assigned']->getId(),],//$people['default']->getFirstName(),
                ];
            }
        }
        // 'assigned' => [
        //     'name' => $people['assigned']->getFirstName(),
        //     'id'   => $people['assigned']->getId(),
        // ],
        // 'default' => [
        //     'name' => $people['assigned']->getFirstName(),
        //     'id'   => $people['assigned']->getId(),],//$people['default']->getFirstName(),
        return $return;
    }

    /**
     * returns JSON-friendlier representation of $assignment
     *
     * @param  Array  $assignment
     * @return Array
     */
    public function assignmentToArray(Array $assignment)
    {
        foreach($assignment as $key => $person) {
            if ($assignment[$key] instanceof Person) {
                $assignment[$key] = $assignment[$key] = [
                    'name' => $assignment[$key]->getFirstName(),
                    'id'   =>  $assignment[$key]->getId(),
                ];
            }
        }
        // will this work, or fuck up the sequence?
        $people = array_map(function ($p){
            return [
                'id' => $p->getPerson()->getId(),
                'name' => $p->getPerson()->getFullName(),
            ];
        },$assignment['rotation']->toArray());

        $assignment['rotation'] = $people;
        $assignment['start_date'] = $assignment['start_date']->format('Y-m-d');
        if (!empty($assignment['substitution'])) {
            $sub = $assignment['substitution'];
            $assignment['substitution_id'] = $sub->getId();
            $assignment['substitution_duration'] = $sub->getDuration();

        } else {
            $assignment['substitution_id'] = null;
            $assignment['substitution_duration'] = null;
        }
        unset($assignment['substitution']);

        return $assignment;
    }

    /**
     * gets entity manager
     *
     * @return EntityManagerInterface
     */
    public function getEntityManager() : EntityManagerInterface
    {
        return $this->em;
    }
}
