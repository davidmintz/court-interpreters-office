<?php /** module/Rotation/src/Service/TaskRotationService.php */
declare(strict_types=1);

namespace InterpretersOffice\Admin\Rotation\Service;

use Doctrine\ORM\EntityManagerInterface;
use DateTime;
use InterpretersOffice\Admin\Rotation\Entity;
//use InterpretersOffice\Admin\Rotation\Entity\RotationRepository;

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

    public function __construct(EntityManagerInterface $em, Array $config)
    {
        $this->em = $em;
        $this->config = $config;
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
                $assignment = $repo->getAssignedPerson($task, $date);
                $result[$type][$task->getName()] = $assignment;
            }
        }

        return $result;
    }
}
