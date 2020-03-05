<?php /** module/Notes/src/Service/NotesService.php */
declare(strict_types=1);

namespace InterpretersOffice\Admin\Notes\Service;

use Doctrine\ORM\EntityManagerInterface;
use Laminas\Authentication\AuthenticationServiceInterface as AuthService;
use InterpretersOffice\Admin\Notes\Entity\NoteInterface;
use InterpretersOffice\Admin\Notes\Entity\MOTD;
use InterpretersOffice\Admin\Notes\Entity\MOTW;
use InterpretersOffice\Admin\Notes\Entity\MOTDRepository;
use InterpretersOffice\Admin\Rotation\Entity\RotationRepository;
use DateTime;
use Laminas\Session\Container as SessionContainer;

use Laminas\InputFilter\InputFilter;
use Laminas\Validator;
use Laminas\Filter;
use InterpretersOffice\Entity;
use Parsedown;
use InterpretersOffice\Admin\Service\MarkdownTrait;
use Laminas\Http\Header\HeaderInterface;
/**
 * manages MOTW|MOTDs
 */
class NotesService
{
    /**
     * entity manager
     *
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * user
     *
     * @var stdClass $user
     */
    private $user;

    /**
     * Notes repository
     * @var MOTDRepository
     */
    private $noteRepository;

    /**
     * session settings
     *
     * @var SessionContainer
     */
    private $session;

    /**
     * whether to fetch task-assignments with MOT[DW]
     *
     * @var boolean
     */
    private $include_task_rotation;

    /**
     * default settings for MOTD|MOTW
     *
     * @var array
     */
    public static $default_settings = [
        'date' => null,
        'motd' => [
            'visible' => false,
            'size' => [
                'width'=>'320px',
                'height'=>'250px',
            ],
            'position' => [
                'left' => '25px',
                'top' => '120px',
            ],
        ],
        'motw' => [
            'visible' => false,
            'size' => [
                'width'=>'320px',
                'height'=>'250px',
            ],
            'position' => [
                'left' => '25px',
                'top' => '150px',
            ],
        ],
    ];

    /**
     * inputfilter for MOT[DW]
     *
     * @var InputFilter
     */
    private $inputFilter;



    /**
     * for configuring optional behavior
     *
     * @var array
     */
    private $options;

    use MarkdownTrait;

    /**
     * constructor
     *
     * @param EntityManagerInterface $em
     * @param AuthService            $auth
     * @param Array $options
     */
    public function __construct(EntityManagerInterface $em, AuthService $auth, Array $options = [])
    {
        $this->em = $em;
        $this->user = $auth->getIdentity();
        $this->options = $options;
    }

    /**
     * gets options
     *
     * @return array
     */
    public function getOptions() : Array
    {
        return $this->options;
    }

    public function getInputFilter() : InputFilter
    {

        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $inputFilter->add(
                [
                    'name' => 'content',
                    'required' => true,
                    'validators' => [
                        [
                            'name' => Validator\NotEmpty::class,
                            'options' => [
                                'messages' => [
                                    Validator\NotEmpty::IS_EMPTY => "some message text is required",
                                ]
                            ],
                            'break_chain_on_failure' => true,
                        ],
                        [
                            'name' => Validator\StringLength::class,
                            'options' => [
                                'min' => 5, 'max' => 1800,
                                'messages' => [
                                     Validator\StringLength::TOO_SHORT => 'message has to be a minimum of %min% characters',
                                     Validator\StringLength::TOO_LONG => 'message cannot exceed a maximum of %max% characters',
                                ]
                            ]
                        ]
                    ],
                    'filters' => [
                        [ 'name' => Filter\StringTrim::class,],
                        // strip trailing spaces before line break, and use css for
                        // linebreaks within block-level elements
                        ['name' => Filter\PregReplace::class,
                            'options'=> [
                                'pattern' => '/( {2,})(\R)/m',
                                'replacement' => "$2",
                            ],
                        ],
                    ],
                ]
            );
            $inputFilter->add(
                [
                    'name' => 'csrf',
                    'required' => true,
                    'validators' => [
                        [
                            'name' => Validator\NotEmpty::class,
                            'options' => [
                                'messages' => [
                                    Validator\NotEmpty::IS_EMPTY => "required security token is missing",
                                ]
                            ],
                        ],
                        [
                            'name' => Validator\Csrf::class,
                            'options' => [
                                'messages' =>
                                    [
                                        'notSame' => 'Security error: invalid/expired CSRF token.'
                                        .' Please reload the page and try again.',
                                    ],
                                'timeout' => 600,
                            ],
                        ],
                    ]
                ]
            );
            $inputFilter->add([
                'name' => 'modified',
                'required' => true,
                'validators' => [
                    [
                        'name' => Validator\NotEmpty::class,
                        'options' => [
                            'messages' => [
                                Validator\NotEmpty::IS_EMPTY => "required modification timestamp is missing",
                            ]
                        ],
                    ],
                ],
            ]);
            $inputFilter->add([
                'name' => 'type',
                'required' => true,
                'validators' => [
                    [
                        'name' => Validator\NotEmpty::class,
                        'options' => [
                            'messages' => [
                                Validator\NotEmpty::IS_EMPTY => "message type is required",
                            ],
                        ],
                    ],
                    [
                        'name' => Validator\InArray::class,
                        'options' => [
                            'haystack' => ['motd','motw'],
                            'messages' => [
                                Validator\InArray::NOT_IN_ARRAY =>
                                 'invalid type: %value%',
                            ],
                        ],
                        'break_chain_on_failure' => true,
                    ],
                ],
                'filters' => [
                    [ 'name' => 'StringTrim'],
                    [ 'name' => 'StringToLower'],
                ],
            ]);
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    /**
     * adds date validation
     *
     * @param  InputFilter $inputFilter
     * @param  string      $type either 'motd' or 'motw'
     * @return InputFilter
     */
    public function addDateValidation(InputFilter $inputFilter,string $type) : InputFilter
    {
        $validators = [
            [
                'name' => 'NotEmpty',
                'options' => ['messages'=>['isEmpty'=>'date is required']],
                'break_chain_on_failure' => true,
            ],
            [
                'name' => 'Date',
                'options' => ['messages'=>['dateInvalidDate'=>'date is invalid']],
                'break_chain_on_failure' => true,
            ],
        ];
        if ($type == 'motd') {
            $spec = [
                'name' => 'date',
                'required' => true,
                'validators' => $validators,
            ];
        } elseif ($type == 'motw') {
            $validators[]= [
                'name' =>'Callback',
                'options' => [
                    'messages' => ['callbackValue' => 'date for MOTD should be a Monday'],
                    'callBack' => function($value){
                        return 1 == (new \DateTime($value))->format('N');
                    },
                ],
            ];
            $spec = ['name' => 'week_of','required' => true, 'validators'=>$validators];
        } else {
            throw new \RuntimeException("invalid entity type: '$type'");
        }
        $inputFilter->add($spec);

        return $inputFilter;
    }

    /**
     * sets session container
     *
     * @param SessionContainer $session
     * @return NotesService
     */
    public function setSession(SessionContainer $session) : NotesService
    {
        $this->session = $session;

        return $this;
    }

    /**
     * updates MOTD|MOTW settings
     *
     * @param  Array $params values to use to update settings
     * @return Array $settings newly updated settings
     */
    public function updateSettings(Array $params): Array
    {
        $session = $this->getSession();
        $settings = $session->settings ?? self::$default_settings;
        $keys = ['visible','position','size'];
        foreach (['motd','motw'] as $type) {
            if (isset($params[$type])) {
                foreach($keys as $key) {
                    if (isset($params[$type][$key])) {
                        $settings[$type][$key] = $params[$type][$key];
                    }
                }
            }
        }
        if (isset($params['date'])) {
            $settings['date'] = $params['date'];
        }
        $session->settings = $settings;

        return $settings;
    }

    /**
     * gets the session container
     *
     * @return SessionContainer [description]
     */
    public function getSession() : SessionContainer
    {
        if (! $this->session) {
            $this->session = new SessionContainer('notes');
        }

        return $this->session;
    }

    public function getMOTD($id) :? NoteInterface
    {
        return $this->getRepository()->find($id);
    }

    public function getMOTW($id) :? NoteInterface
    {
        return $this->em->getRepository(MOTW::class)
            ->find($id);
    }

    /**
     * updates MOT[DW]
     *
     * @param  string $type MOTD|MOTW
     * @param  int    $id
     * @param  Array  $data
     * @return Array
     */
    public function update(string $type, int $id, Array $data) : Array
    {
        $entity = $this->{'get'.\strtoupper($type)}($id); // legible, huh?
        if (!$entity) {
            return [
                'status' => 'error',
                'message' => "$type with id $id not found",
            ];
        }
        $content_before = $entity->getContent();
        if ($data['content'] == $content_before) {
            return [$type => $entity, 'status'=>'success','message'=>'not modified'];
        }
        if ($entity->getModified() &&
            $entity->getModified()->format('Y-m-d H:i:s') != $data['modified'])
        {
            return [$type => $entity,'status' => 'error',
            'modified' => $entity->getModified()->format('Y-m-d H:i:s'),
            'message'=> "This $type has been modified by another process in the time since you loaded this form."];
        }
        $user = $this->em->getRepository(Entity\User::class)->find($this->user->id);
        $entity->setContent($this->escape($data['content']))
            ->setModifiedBy($user)
            ->setModified(new DateTime());
        $this->em->flush();

        return [$type => $entity, 'status'=>'success'];
    }

    /**
     * deletion
     *
     * @param  string $id
     * @return null
     */
    public function delete(string $type, string $id, string $token)  {

        $filter = $this->getInputFilter();
        $filter->setValidationGroup(['csrf','type']);
        $filter->setData(['csrf'=>$token,'type'=>$type]);
        if (! $filter->isValid()) {
            return [
                'validation_errors'=>$filter->getMessages(),
                'status'=>'validation failed'
            ];
        }
        $entity = $this->{'get'.\strtoupper($type)}($id);
        if ($entity) {
            $this->em->remove($entity);
            $this->em->flush();
            $status = 'deleted';
        } else {
            $status = 'not found';
        }
        return ['status' => $status, 'id' => $id];
    }


    public function createBatchEditInputFilter() : InputFilter
    {
        $inputFilter = new InputFilter();
        $inputFilter->add([
            'name' => 'csrf',
            'required' => true,
            'validators' => [
                [
                    'name' => Validator\NotEmpty::class,
                    'options' => [
                        'messages' => [
                            Validator\NotEmpty::IS_EMPTY => "required security token is missing",
                        ]
                    ],
                ],
                [
                    'name' => Validator\Csrf::class,
                    'options' => [
                        'messages' =>
                            [
                                'notSame' => 'Security error: invalid/expired CSRF token.'
                                .' Please reload the page and try again.',
                            ],
                        'timeout' => 600,
                    ],
                ],
            ]
        ]);
        $inputFilter->add([
            'name'=>'text','required'=>true,
            'validators' => [
                [
                    'name' => Validator\NotEmpty::class,
                    'options' => [
                        'messages' => [
                            Validator\NotEmpty::IS_EMPTY => "text is required",
                        ]
                    ],
                ],
            ],
            'filters' =>  [
                [ 'name' => Filter\StringTrim::class,],
                // strip trailing spaces before line break, and use css for
                // linebreaks within block-level elements
                ['name' => Filter\PregReplace::class,
                'options'=> [
                    'pattern' => '/( {2,})(\R)/m',
                    'replacement' => "$2",
                    ],
                ],
            ]
        ]);
        $inputFilter->add([
            'name'=>'dates','required'=>true,
            'validators' => [
                [
                    'name'=>'NotEmpty',
                    'options' => [
                         'messages' => ['isEmpty'=> 'at least one date is required']
                    ],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name' => 'Callback',
                    'options' => [
                        'callBack' => function($value, $context) {
                            return is_array($value);
                        },
                        'messages' => [
                            'callbackValue' => 'MOTD dates must be an array'
                        ],
                    ],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name' => 'Callback',
                    'options' => [
                        'callBack' => function($value) {
                            return count($value) <= 20;
                        },
                        'messages' => [
                            'callbackValue' => 'more than 20 MOTDs at once is ill-advised'
                        ],
                    ],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name' => 'Callback',
                    'options' => [
                        'callBack' => function($value, $context) {

                            $validator = new Validator\Date(['format'=>'Y-m-d']);
                            foreach ($value as $date) {
                                if (!$validator->isValid($date)) {
                                    return false;
                                }
                            }
                            return true;
                        },
                        'messages' => [
                            'callbackValue' => 'malformed MOTD date found'
                        ],
                    ],
                ],
            ],
        ]);

        $inputFilter->add(
            [
                'name'=>'position',
                'required' => true,
                'validators' => [
                    [
                        'name' => Validator\NotEmpty::class,
                        'options' => [
                            'messages' => [
                                Validator\NotEmpty::IS_EMPTY => "position is required",
                            ]
                        ],
                        'break_chain_on_failure' => true,
                    ],
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => ['append','prepend'],
                            'messages' => [
                                'notInArray' => 'position must be either "append" or "prepend"'
                            ],
                        ],
                        'break_chain_on_failure' => true,
                    ],
                ],
            ]
        );

        return $inputFilter;
    }

    /**
     * batch-processes MOTDs for multiple dates
     *
     * work in progress.
     *
     * @param  Array  $data
     * @param  int $id
     * @return Array
     */
    public function batchEdit(Array $data) : Array
    {

        $batchFilter = $this->createBatchEditInputFilter();
        // first validate the multi-date input
        $batchFilter->setData($data);
        if (! $batchFilter->isValid()) {
            $result['validation_errors'] = $batchFilter->getMessages();
            return $result;
        }
        $result = ['data'=>$batchFilter->getValues()];
        // get MOTDs for $data['dates']
        $dates = $batchFilter->getValue('dates');
        $entities = $this->getRepository()->getBatch($dates);
        $inputFilter = $this->getInputFilter()->setValidationGroup('content');
        $text = $batchFilter->getValue('text');
        $position = $batchFilter->getValue('position');
        $created = $updated = 0;
        foreach ($dates as $d) {
            if (isset($entities[$d])) {
                $motd = $entities[$d];
            } else {
                $motd = new MOTD();
            }
            if ($position == 'append') {
                $content = $motd->getContent() . PHP_EOL . $text;
            } else {
                $content =  $text . PHP_EOL . $motd->getContent();
            }
            $inputFilter->setData(['content'=>$content]);
            if (! $inputFilter->isValid()) {
                $result['validation_errors'] = $inputFilter->getMessages();
                return $result;
            } // else ...
            $user = $this->em->getRepository(Entity\User::class)->find($this->user->id);
            $now = new DateTime();
            $motd->setContent($this->escape($inputFilter->getValue('content')))
                ->setModifiedBy($user)
                ->setModified($now);
            if (! $motd->getId()) {
                // new entity
                $motd->setDate(new DateTime($d))
                    ->setCreated($now)->setCreatedBy($user);
                $this->em->persist($motd);
                $created++;
            } else {
                $updated++;
            }
        }
        $this->em->flush();
        $result['status'] = 'success';
        $result['created'] = $created;
        $result['updated'] = $updated;

        return $result;
    }

    public function create(Array $data) : Array
    {

        $class = 'InterpretersOffice\\Admin\\Notes\\Entity\\'.strtoupper($data['type']);
        $entity = new $class;
        $user = $this->em->getRepository(Entity\User::class)->find($this->user->id);
        $now = new DateTime();
        $entity->setContent($this->escape($data['content']))
            ->setCreated($now)
            ->setDate(new DateTime($data['date']))
            ->setCreatedBy($user)
            ->setModifiedBy($user)
            ->setModified($now);
        $this->em->persist($entity);
        $this->em->flush();

        return [$data['type'] => $entity, 'status'=>'success'];

    }

    /**
     * gets Repository
     *
     * @return MOTDRepository
     */
    public function getRepository() : MOTDRepository
    {
        if (! $this->noteRepository) {
            $this->noteRepository = $this->em->getRepository(MOTD::class);
        }

        return $this->noteRepository;
    }

    /**
     * gets MOT(D|W) by date
     *
     * @param  DateTime $date
     * @param  string   $type
     * @param boolean $render_markdown
     * @return NoteInterface|null
     */
    public function getNoteByDate(DateTime $date, string $type, bool $render_markdown = true) :? NoteInterface
    {
        $entity = $this->getRepository()->findByDate($date,$type);
        if ($entity && $render_markdown) {
            $content = $entity->getContent();
            $entity->setContent($this->parsedown($content));

        }

        return $entity;
    }

    /**
     * gets both MOTD and MOTW for $date
     *
     * @param  DateTime $date
     * @param boolean $render_markdown
     * @return Array
     */
    public function getAllForDate(DateTime $date, bool $render_markdown = true) : Array
    {
        $notes = $this->getRepository()->getAllForDate($date);
        if ($render_markdown) {
            foreach ($notes as $type => $entity) {
                if (! $entity) { continue; }
                $entity->setContent($this->parsedown($entity->getContent()));
            }
        }

        return $notes;
    }

    /**
     * sets $include_task_rotation
     *
     * @var bool
     */
    public function setIncludeTaskRotation(bool $flag) : NotesService
    {
        $this->include_task_rotation = $flag;

        return $this;
    }

    /**
     * gets $include_task_rotation flag
     *
     * @return bool
     */
    public function getIncludeTaskRotation() : bool
    {
        return $this->include_task_rotation ? true : false;
    }
}
