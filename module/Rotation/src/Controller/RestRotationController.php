<?php
/** module/Rotation/src/Controller/RestRotationController.php */

namespace InterpretersOffice\Admin\Rotation\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use InterpretersOffice\Admin\Rotation\Service\TaskRotationService;
use InterpretersOffice\Admin\Rotation\Entity\Task;

/**
 * RESTful controller for Tasks/Rotations
 */
class RestRotationController extends AbstractRestfulController
{
    /**
     * task-rotation service
     *
     * @var TaskRotationService
     */
    protected $service;

    /**
     * constructor
     *
     * @param TaskRotationService $service
     */
    public function __construct(TaskRotationService $service)
    {
        $this->service = $service;
    }

    /**
     * creates a new task rotation
     *
     * @param  Array $data
     * @return JsonModel
     */
    public function create($data)
    {
        return new JsonModel(['status' => 'yet to be implemented']);
    }

    /**
     * gets task assignment
     *
     * @return JsonModel
     */
    public function get($id)
    {
        $date = $this->params()->fromRoute('date');

        return new JsonModel($this->service->getAssignment($date,(int)$id));
    }

    /**
     * updates
     *
     * @param  $id  entity id
     * @param  array $data
     *
     * @return JsonModel
     */
    public function update($id, $data)
    {
        return new JsonModel(['status' => 'yet to be implemented']);
    }
}
