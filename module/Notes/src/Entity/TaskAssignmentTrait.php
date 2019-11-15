<?php /** module/Notes/src/Entity/TaskAssignmentTrait.php */

namespace InterpretersOffice\Admin\Notes\Entity;

use NoteInterface;

trait TaskAssignmentTrait {

    protected $task_assignments = [];

    public function  setTaskAssignments(Array $tasks)
    {
        $this->task_assignments = $tasks;

        return $this;
    }

    public function  getTaskAssignments() : Array
    {
        return $this->task_assignments;
    }

    public function  getTaskAssignmentsJson() : string
    {
        // $assignments =  $this->task_assignments;
        // if (!$assignments) {
        //     return json_encode([]);
        // }
        return json_encode(['shit'=>'somebody']);
    }
}
