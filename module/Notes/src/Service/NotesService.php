<?php /** module/Notes/src/Service/NotesService.php */
declare(strict_types=1);

namespace InterpretersOffice\Admin\Notes\Service;

use Doctrine\ORM\EntityManagerInterface;
use Zend\Authentication\AuthenticationServiceInterface as AuthService;
use InterpretersOffice\Admin\Notes\Entity\NoteInterface;
use InterpretersOffice\Admin\Notes\Entity\MOTD;
use InterpretersOffice\Admin\Notes\Entity\MOTDRepository;
use DateTime;
use Zend\Session\Container as SessionContainer;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Input;
use Zend\Validator;
use Zend\Validator\NotEmpty;
//use Zend\Validator\NotEmpty;

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
    private $auth;

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
                        ],
                    ],
                    'filters' => [

                    ],
                ]
            );
            $inputFilter->add(
                [
                    'name' => 'csrf',
                    'required' => true,
                    // etc
                ]
            );
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    /**
     * sets session container
     *
     * @param SessionContainer $session
     */
    public function setSession(SessionContainer $session)
    {
        $this->session = $session;
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
        return $this->getEntityManager()->getRepository(MOTW::class)
            ->find($id);
    }

    /**
     * updates MOT[DW]
     *
     * work in progress. still have to update meta.
     *
     * @param  string $type MOTD|MOTW
     * @param  int    $id
     * @param  Array  $data
     * @return Array
     */
    public function update(string $type, int $id, Array $data)
    {
        $entity = $this->{'get'.\strtoupper($type)}($id);
        if (!$entity) {
            return [
                'status' => 'error',
                'message' => "$type with id $id not found",
            ];
        }
        $entity->setContent($data['content']);
        $this->em->flush();
        return [$type => $entity, 'status'=>'success'];


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
        return $this->getRepository()->findByDate($date,$type);
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
}
