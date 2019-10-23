<?php /** module/Notes/src/Service/NotesService.php */
declare(strict_types=1);

namespace InterpretersOffice\Admin\Notes\Service;

use Doctrine\ORM\EntityManagerInterface;
use Zend\Authentication\AuthenticationServiceInterface as AuthService;
use InterpretersOffice\Admin\Notes\Entity\NoteInterface;
use InterpretersOffice\Admin\Notes\Entity\MOTD;
use InterpretersOffice\Admin\Notes\Entity\MOTW;
use InterpretersOffice\Admin\Notes\Entity\MOTDRepository;
use DateTime;
use Zend\Session\Container as SessionContainer;

use Zend\InputFilter\InputFilter;
use Zend\Validator;
use Zend\Filter;
use InterpretersOffice\Entity;

use Parsedown;

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
     * constructor
     *
     * @param EntityManagerInterface $em
     * @param AuthService            $auth
     */
    public function __construct(EntityManagerInterface $em, AuthService $auth)
    {
        $this->em = $em;
        $this->user = $auth->getIdentity();
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
                        [ 'name' => Filter\StripTags::class,],
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
     * work in progress. still need to check modification timestamp.
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
        $entity->setContent($data['content'])
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
        $entity->setContent($data['content'])
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
     * @return NoteInterface|null
     */
    public function getNoteByDate(DateTime $date, string $type) :? NoteInterface
    {
        $entity = $this->getRepository()->findByDate($date,$type);
        if ($entity) {
            // $content = $entity->getContent();
            // $entity->setContent($this->parsedown($content));
        }

        return $entity;
    }

    /**
     * gets both MOTD and MOTW for $date
     *
     * @param  DateTime $date
     * @return Array
     */
    public function getAllForDate(DateTime $date) : Array
    {
        return $this->getRepository()->getAllForDate($date);
    }

    public function parsedown(string $content) : string
    {
        if (! $this->parseDown) {
            $this->parseDown = new Parsedown();
        }
        return $this->parseDown->text(
            nl2br(
                $content
            )
        );
    }

}
