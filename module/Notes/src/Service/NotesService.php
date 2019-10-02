<?php /** module/Notes/src/Service/NotesService.php */
declare(strict_types=1);

namespace InterpretersOffice\Admin\Notes\Service;

use Doctrine\ORM\EntityManagerInterface;
use Zend\Authentication\AuthenticationServiceInterface as AuthService;
use InterpretersOffice\Admin\Notes\Entity\NoteInterface;
use  InterpretersOffice\Admin\Notes\Entity\MOTD;
use  InterpretersOffice\Admin\Notes\Entity\MOTDRepository;
use DateTime;
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

    /**
     * gets Repository
     *
     * @return NoteRepository
     */
    private function getRepository()//: MOTDRepository
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
