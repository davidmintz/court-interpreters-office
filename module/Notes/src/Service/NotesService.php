<?php /** module/Notes/src/Service/NotesService.php */
declare(strict_types=1);

namespace InterpretersOffice\Admin\Notes\Service;

use Doctrine\ORM\EntityManagerInterface;
use Zend\Authentication\AuthenticationServiceInterface as AuthService;
use InterpretersOffice\Admin\Notes\Entity\NoteInterface;

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

    public function getNoteByDate(string $type,DateTime $date) :? NoteInterface
    {
        
    }
}
