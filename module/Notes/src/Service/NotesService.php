<?php /** module/Notes/src/Service/NotesService.php */
declare(strict_types=1);

namespace InterpretersOffice\Admin\Notes\Service;

use Doctrine\ORM\EntityManagerInterface;
use Zend\Authentication\AuthenticationServiceInterface as AuthService;
use InterpretersOffice\Admin\Notes\Entity\NoteInterface;
use InterpretersOffice\Admin\Notes\Entity\MOTD;
use InterpretersOffice\Admin\Notes\Entity\MOTW;
use InterpretersOffice\Admin\Notes\Entity\MOTDRepository;
use InterpretersOffice\Admin\Rotation\Entity\RotationRepository;
use DateTime;
use Zend\Session\Container as SessionContainer;

use Zend\InputFilter\InputFilter;
use Zend\Validator;
use Zend\Filter;
use InterpretersOffice\Entity;

use Parsedown;
use Zend\Filter\HtmlEntities;

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
     * htmlentity filter
     *
     * @var Filter\HtmlEntities
     */
    private $htmlentity_filter;

    /**
     * session settings
     *
     * @var SessionContainer
     */
    private $session;

    /**
     * whether to fetch task-assignments with MOT[DW]
     *
     * @var book
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
     * Parsedown
     * @var Parsedown
     */
    private $parseDown;

    /**
     * for configuring optional behavior
     *
     * @var array
     */
    private $options;

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

    /**
     * escapes $content
     *
     * @return string
     */
    private function escape(string $content) : string
    {
        if (! $this->htmlentity_filter) {
            $this->htmlentity_filter = new Filter\HtmlEntities();
        }

        return $this->htmlentity_filter->filter($content);
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
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
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

    public function getMOTD($id)
    {
        return $this->getRepository()->find($id);
    }

    public function getMOTW($id)
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
    public function update(string $type, int $id, Array $data)
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
            if ($this->options['display_rotating_assignments'][strtolower($type)]) {
                $this->injectTaskAssignments($entity,$date);
            }
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
                if ($this->options['display_rotating_assignments'][strtolower($type)]) {
                    $this->injectTaskAssignments($entity,$date);
                }
            }
        }

        return $notes;
    }

    /**
     * helper to set the task assignment on $entity
     * 
     * @param  NoteInterface $entity
     * @param  DateTime      $date
     * @return NoteInterface
     */
    private function injectTaskAssignments(NoteInterface $entity, DateTime $date) : NoteInterface
    {
        /** @var InterpretersOffice\Admin\Rotation\Entity\RotationRepository */
        $task_repo = $this->em->getRepository('InterpretersOffice\Admin\Rotation\Entity\Rotation');
        $assignments = [];
        $type = strstr(get_class($entity),'MOTD') ? 'motd': 'motw';
        $ids = $this->options['display_rotating_assignments'][$type];
        foreach ($ids as $task_id) {
            $task = $this->em->find('InterpretersOffice\Admin\Rotation\Entity\Task',$task_id);
            $assignment = $task_repo->getAssignedPerson($task, $date);
            $assignments[$task->getName()] = $assignment;
        }
        $entity->setTaskAssignments($assignments);

        return $entity;
    }

    /**
     * renders markdown as HTML
     *
     * @param  string $content
     * @return string
     */
    public function parsedown(string $content) : string
    {
        if (! $this->parseDown) {
            $this->parseDown = new Parsedown();
        }

        return $this->parseDown->text($content);
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
     * sets $include_task_rotation flag
     *
     * @return bool
     */
    public function getIncludeTaskRotation() : bool
    {
        return $this->include_task_rotation ? true : false;
    }
}
