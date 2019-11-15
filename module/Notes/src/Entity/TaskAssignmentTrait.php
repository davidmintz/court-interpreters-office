<?php /** module/Notes/src/Entity/TaskAssignmentTrait.php */

namespace InterpretersOffice\Admin\Notes\Entity;

use NoteInterface;

/**
 * for implementing NoteInterface
 */
trait TaskAssignmentTrait {

    /**
     * array of task assignments
     *
     * @var Array
     */
    protected $task_assignments = [];

    /**
     * sets task assignments
     *
     * @param  Array         $tasks
     * @return NoteInterface
     */
    public function  setTaskAssignments(Array $tasks) : object
    {
        $this->task_assignments = $tasks;

        return $this;
    }

    /**
     * gets task assignments
     *
     * @return Array
     */
    public function  getTaskAssignments() : Array
    {
        return $this->task_assignments;
    }

    /**
     * gets task assignments as JSON
     *
     * @return string
     */
    public function  getTaskAssignmentsJson() : string
    {
        if (!$this->task_assignments) {
            return json_encode([]);
        }
        $return = [];
        foreach ($this->task_assignments as $task => $assignment) {
            $return[$task] = [
                'assigned' => $assignment['assigned']->getFirstName(),
                'default' => $assignment['default']->getFirstName(),
            ];
        }

        return json_encode($return);
    }
}
