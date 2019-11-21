<?php /** module/Rotation/src/Service/TaskRotationService.php */
declare(strict_types=1);

namespace InterpretersOffice\Admin\Rotation\Service;

use Doctrine\ORM\EntityManagerInterface;
use DateTime;
use InterpretersOffice\Admin\Rotation\Entity;
use InterpretersOffice\Admin\Rotation\Entity\RotationRepository;
use Zend\EventManager\EventInterface;
use Zend\View\Model\JsonModel;

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
                    $assignment = $repo->getAssignedPerson($task, $date);
                    $result[$type][$task->getName()] = $assignment;
                }
            }
        }

        return $result;
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
                $view->assignment_notes = $this->assignmentNotesToJson($assignment_notes);
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
    public function assignmentNotesToJson(Array $assignment_notes): Array
    {
        $return = [];
        foreach ($assignment_notes as $note_type => $data) {
            $return[$note_type] = [];
            foreach($data as $task => $people) {
                $return[$note_type][$task] = [
                    'assigned' => $people['assigned']->getFirstName(),
                    'default' => $people['default']->getFirstName(),
                ];
            }
        }

        return $return;
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
