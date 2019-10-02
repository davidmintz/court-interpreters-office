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

    public static $default_settings = [
        'date' => null,
        'motd' => [
            'visible' => false,
            'size' => [
                'width'=>'320px',
                'height'=>'250px',
            ],
            'position' => [
                'left' => '50px',
                'top' => '50px',
            ],
        ],
        'motw' => [
            'visible' => false,
            'size' => [
                'width'=>'320px',
                'height'=>'250px',
            ],
            'position' => [
                'left' => '50px',
                'top' => '150px',
            ],
        ],
    ];



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

    /**
     * gets Repository
     *
     * @return NoteRepository
     */
    private function getRepository() : MOTDRepository
    {
        if (! $this->noteRepository) {
            $this->noteRepository = $this->em->getRepository(MOTD::class);
        }

        return $this->noteRepository;
    }

    public function getNoteByDate(DateTime $date, string $type) :? NoteInterface
    {
        return $this->getRepository()->findByDate($date,$type);
    }

    /**
     * gets MOTD and MOTW for $date
     *
     * @param  DateTime $date
     * @return Array
     */
    public function findAllForDate(DateTime $date) : Array
    {
        return $this->getRepository()->findAllForDate($date);
    }
}
