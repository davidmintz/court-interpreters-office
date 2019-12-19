<?php /** module/Rotation/src/Service/TaskRotationService.php */
declare(strict_types=1);

namespace InterpretersOffice\Admin\Rotation\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use DateTime;
use InterpretersOffice\Admin\Rotation\Entity;
use InterpretersOffice\Entity\Person;
use InterpretersOffice\Admin\Rotation\Entity\Substitution;
use InterpretersOffice\Admin\Rotation\Entity\RotationRepository;
use Zend\EventManager\EventInterface;
use Zend\View\Model\JsonModel;

use Zend\InputFilter;
use Zend\Validator;
use Zend\Filter;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

/**
 * TaskRotationService
 */

/*
handy snippet:
SELECT s.id, t.name AS task, s.date,
(SELECT p.firstname FROM people p
    JOIN task_rotation_members m ON p.id = m.person_id
    JOIN rotations r ON m.rotation_id = r.id
WHERE m.rotation_order =  FLOOR(DATEDIFF(s.date,r.start_date)/7) %
    (SELECT COUNT(*) FROM task_rotation_members m
    WHERE m.rotation_id = s.rotation_id)
    AND m.rotation_id = s.rotation_id
) AS `default`,
p.firstname AS assigned
FROM rotation_substitutions s JOIN people p ON s.person_id = p.id
JOIN rotations r ON r.id = s.rotation_id JOIN tasks t ON r.task_id = t.id
ORDER BY s.date;
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


    public function getTaskInputFilter()
    {
        $inputFilter = new InputFilter\InputFilter();
        //$inputFilter->add($this->getRotationInputFilter(), 'rotation');
        $inputFilter->add([
            'name' => 'frequency',
            'required' => true,
            'validators' => [
                [
                    'name' => 'NotEmpty',
                    'options' => [
                        'messages' => ['isEmpty'=> '"frequency" field is required'],
                    ],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name' => 'InArray',
                    'options' => [
                        'haystack' => [ "WEEK"], //"DAY",...
                        'messages' => [
                            'notInArray' => 'the only supported frequency is weekly'
                        ],
                    ],
                    'break_chain_on_failure' => true,
                ],
            ]
        ]);
        $inputFilter->add([
            'name' => 'name',
            'required' => true,
            'validators' => [
                [
                    'name' => 'NotEmpty',
                    'options' => [
                        'messages' => ['isEmpty'=> 'a name for the task is required'],
                    ],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name' => Validator\StringLength::class,
                    'options' => [
                        'min' => 4, 'max' =>30,
                        'messages' => [
                             Validator\StringLength::TOO_SHORT => 'task name has to be a minimum of %min% characters',
                             Validator\StringLength::TOO_LONG => 'task name cannot exceed a maximum of %max% characters',
                        ]
                    ],
                    'break_chain_on_failure' => true,
                ]
                // .....
            ],
        ]);
        $inputFilter->add([
            'name' => 'description',
            'required' => false,
            //'allow_empty' => true,
            'validators' => [
                [
                    'name' => Validator\StringLength::class,
                    'options' => [
                        'min' => 12, 'max' =>400,
                        'messages' => [
                             Validator\StringLength::TOO_SHORT => 'task description has to be a minimum of %min% characters',
                             Validator\StringLength::TOO_LONG => 'task description cannot exceed a maximum of %max% characters',
                        ]
                    ],
                    'break_chain_on_failure' => true,
                ]
            ],
        ]);
        $inputFilter->add([
            'name' => 'day_of_week',
            'required' => true,
            //'allow_empty' => false,
            'validators' => [
                [
                    'name' => 'NotEmpty',
                    'options' => [
                        'messages' => ['isEmpty'=> 'day of week is required'],
                    ],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name' => 'InArray',
                    'options' => [
                        'haystack' => range(0,6), //"DAY",...
                        'messages' => [
                            'notInArray' => 'day of week has be a value between 0 and 6',
                        ],
                    ],
                    'break_chain_on_failure' => true,
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
            ]
        ]);

        return $inputFilter;
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
        $inputFilter->add(
            [
                'name' => 'members',
                'type' => InputFilter\ArrayInput::class,
                'required' => true,
                'validators' =>[
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages' => [
                                'isEmpty' => 'rotation (names of people) is required'
                            ],
                        ],
                        'break_chain_on_failure' => true,
                    ],
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
                    'name' => 'NotEmpty',
                    'options' => [
                        'messages' => [
                            'isEmpty' => 'rotation (names of people) is required'
                        ],
                    ],
                    'break_chain_on_failure' => true,
                ],
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
                            $today = date('Y-m-d');
                            return  $date >= $today;

                        },
                        'messages' => [
                            'callbackValue' => 'retroactive creation or modification of tasks and rotations is not supported',
                        ],
                    ],
                ],
            ],
        ]);
        $inputFilter->add(
            [
                'name'=>'task',
                'required' => true,
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages' => [
                                'isEmpty' => 'task is required'
                            ],
                        ],
                        'break_chain_on_failure' => true,
                    ],
                    [
                        'name' => 'Callback',
                        'options' => [
                            'callBack' => function($value, $context) use ($em) {

                                $task = $em->find(Entity\Task::class,$value);
                                if (! $task) { return false; }
                                return true;
                            },
                            'messages' => [
                                'callbackValue' => 'no such Task was found in the database',
                            ],
                        ],
                        'break_chain_on_failure' => true,
                    ],
                ],
            ]
        );
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
        /** @todo remove this */
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
     * attempts to create a new Rotation
     * @param Array
     * @return Array
     */
    public function createRotation(Array $data)
    {
        $inputFilter = $this->getRotationInputFilter();
        // 'countable' is sort of a pseudo-input, same data as 'members'.
        // This hack is designed to get validators to run the way we want
        $data['countable'] = $data['members'] ?? null;
        $inputFilter->setData($data);
        $valid = $inputFilter->isValid();
        if (! $valid) {
            // but could result in silly duplicate error messages
            $errors = $inputFilter->getMessages();
            if (key_exists('countable',$errors) && key_exists('members',$errors)) {
                if (isset($errors['countable']['isEmpty']) && isset($errors['members']['isEmpty']))
                {
                    unset($errors['countable']);
                }
            }
            return
                [
                    'validation_errors' => $errors,
                    'valid' => false,
                ];
        }
        $values = $inputFilter->getValues();
        $task = $this->em->find(Entity\Task::class,$values['task']);
        $rotation = new Entity\Rotation();
        $rotation->setTask($task)->setStartDate(new DateTime($values['start_date']));
        $dql = 'SELECT p FROM InterpretersOffice\Entity\Person p WHERE p.id IN (:ids)';
        $people = $this->em->createQuery($dql)
            ->setParameters(['ids'=> $values['members']])->getResult();
        $reverse_ids = array_flip($values['members']);
        foreach ($people as $m) {
            $member = new Entity\RotationMember();
            $member->setRotation($rotation)->setPerson($m)
                ->setOrder($reverse_ids[$m->getId()]);
            $rotation->addMember($member);
        }
        $this->em->persist($rotation);
        $this->em->flush();
        return [
            'status'=>'success',
            'valid' => true,
            'rotation' => $inputFilter->getValues(),
        ];
    }

    public function createTask(Array $data)
    {
        $result = ['info' => 'not yet implemented','validation_errors'=>[]];
        $inputFilter = $this->getTaskInputFilter();
        if (isset($data['duration']) && $data['duration'] == 'WEEK') {
            $inputFilter->remove('day_of_week');
        }
        $rotationFilter = $this->getRotationInputFilter();
        $rotationFilter->remove('task');
        $rotationFilter->setData($data['rotation']??[]);
        $valid = true;
        $inputFilter->setData($data);
        if (! $inputFilter->isValid()) {
            $valid = false;
            $result['validation_errors'] = $inputFilter->getMessages();
        }
        if (! $rotationFilter->isValid()) {
            $valid = false;
            $result['validation_errors']['rotation'] = $rotationFilter->getMessages();
        }
        if (!$valid) {
            $result['valid'] = false;
            //return $result;
        }
        //$result['data'] = $data['rotation'];
        return $result;
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
     * does actual insertion of new Substitution
     *
     * @param  Array $data
     * @return Entity\Substitution
     */
    private function doCreate(Array $data) : Entity\Substitution
    {
        //print_r($data);exit();
        $repo = $this->getRepository();
        $rotation = $repo->find($data['rotation_id']);
        $sub = new Entity\Substitution($rotation);
        $person = $this->em->find('InterpretersOffice\Entity\Person',$data['person']);
        if ($data['duration'] == 'WEEK') {
            $date = $repo->getMondayPreceding(new \DateTime($data['date']));
        } else {
            $date = new \DateTime($data['date']);
        }
        $sub->setPerson($person)->setDate($date)->setDuration($data['duration']);
        $this->em->persist($sub);

        return $sub;
    }

    /**
     * handles Substitution for a Task on $data['date']
     *
     * @param  Array $data
     * @return Array
     */
    public function createSubstitution(Array $data) : Array
    {
        /* e.g.,
        date	2019-12-04
        task	2
        person	198
        duration	DAY
        substitution
        rotation_id	14
        */
       //print_r($data);
       $assignment = $this->getAssignment($data['date'], (int)$data['task']);
       // we have a "substitution" key, which, if not null, means there is/was already
       // a sub, but we might as well check anyway
       $debug = '';
      // $result = [];
       if ($data['substitution']) {
           $debug .= "substitution param was provided.\n";
       }
       if (!$assignment['substitution_id']) {
           $debug .= "NO existing substitution found\n";
       } else {
           $debug .= "existing substitution found: {$assignment['substitution_id']}\n";
       }


       $deleted_sub = false;
       /*  is there already a Substitution? if that's the case, we update (or delete) */
       if ($assignment['substitution_id']) {
           /** @var Entity\Substitution $sub */
           $sub = $this->em->find(Entity\Substitution::class,$assignment['substitution_id']);
           // are they are setting it back to the default person?
           if ($data['person'] == $assignment['default']['id']) {
               // are they modifying the duration?
               $is_same_duration = $sub->getDuration() == $data['duration'];
               if ($is_same_duration) {
                   $this->em->remove($sub);
                   $deleted_sub = true;
                   $debug .= "found existing sub, default person and duration same as submitted; deleted substitution\n";
                   unset($assignment['substitution_duration']);
                   unset($assignment['substitution_id']);
                   $assignment['assigned'] = $assignment['default'];
               } else {
                   // different duration; need a new Substitution entity
                   $debug .= "existing sub, person is same as default; duration is different; create new Sub entity?\n";
                   $sub = $this->doCreate($data);
               }
           } else { // not setting back to the default person.
               $debug .= "found existing sub, person submitted is NOT the default, duration is different: updating sub\n";
               $person = $this->em->find('InterpretersOffice\Entity\Person',$data['person']);
               $sub->setPerson($person)->setDuration($data['duration']);
           }
       } else {
            $debug .= "no existing subsitution was found. creating new\n";
            $sub = $this->doCreate($data);
       }
       try {
           //Doctrine\DBAL\Exception\UniqueConstraintViolationException
           $this->em->flush();
       } catch (UniqueConstraintViolationException $e) {
           // find the offending row, and update that one instead
           //UNIQUE KEY `subst_idx` (`date`,`task_id`,`duration`),
           /* ERR (3): An exception occurred while executing
            'UPDATE rotation_substitutions SET duration = ?, person_id = ? WHERE id = ?'
            with params ["WEEK", 862, 161]:
            */
           $debug .= "caught duplicate entry, looking for existing row\n";
           $repo = $this->getRepository();
           $date = new DateTime($data['date']);
           $sub = $this->getRepository()->findOneBy([
                'task'=>$repo->getTask($data['task']),
                'duration' => $data['duration'],
                'date' =>  $data['duration'] == 'WEEK' ?
                        $repo->getMondayPreceding($date)
                        : $date,
            ]);
            if (!isset($person)) {
                $person = $this->em->find('InterpretersOffice\Entity\Person',$data['person']);
            }
            $sub->setPerson($person);
            $this->em->flush();
            $assignment['substitution_id'] = $sub->getId();
            $debug .= sprintf("set %s to substitution id %d, duration %s\n",
                $person->getFirstname(), $sub->getId(),$data['duration']
            );
            /*
            SQLSTATE[23000]: Integrity constraint violation: 1062
            Duplicate entry '2019-12-09-2-WEEK' for key 'subst_idx'

            | 161 |       2 |       198 | 2019-12-09 | DAY
            | 160 |       2 |       840 | 2019-12-09 | WEEK
            */
       }
       //if
       // $assignment['substitution_id'] = isset($sub) ? $sub->getId() : null;
       // $assignment['substitution_duration'] = isset($sub) ? $sub->getDuration() : null;
       if (! $deleted_sub) {
           $assignment['substitution_id'] = $sub->getId();
           $assignment['substitution_duration'] =  $sub->getDuration();
           $assignment['assigned'] = [
               'id' => $sub->getPerson()->getId(),
               'name' => $sub->getPerson()->getFirstname(),
           ];
       }
       // if (isset($person)) {
       //     $assignment['assigned'] = [
       //         'id' => $person->getId(),
       //         'name' => $person->getFirstname(),
       //     ];
       // }

       return ['debug'=>$debug,'assignment'=>$assignment,'notice'=>$notice ?? null];
    }

    /**
     * proxies to  Entity\RotationRepository::getTask()
     *
     * note to self: maybe remove the repo method and do the work here and
     * update all the client code?
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
        //$assignment['rotation_id'] =
        $assignment['start_date'] = $assignment['start_date']->format('Y-m-d');
        if (!empty($assignment['substitution'])) {
            $sub = $assignment['substitution'];
            // $subs = array_map(function($s){
            //     return [
            //         'substitution_id' => $sub->getId(),
            //         'substitution_duration' => $sub->getDuration(),
            //     ];
            // },$assignment['substitution']);//)$assignment['substitution'];
            $assignment['substitution_id'] = $sub->getId();
            $assignment['substitution_duration'] = $sub->getDuration();
            // echo "DEBUG: HELLO! duration is ".$sub->getDuration()."\n";
            unset($assignment['substitution']);// = $sub;
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
