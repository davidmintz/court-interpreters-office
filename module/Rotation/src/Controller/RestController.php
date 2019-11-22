<?php
/** module/Rotation/src/Controller/RestController.php */

namespace InterpretersOffice\Admin\Rotation\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use InterpretersOffice\Admin\Rotation\Service\TaskRotationService;
use InterpretersOffice\Admin\Rotation\Entity\Task;

/**
 * RESTful controller for Tasks/Rotations
 */
class RestController extends AbstractRestfulController
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

    public function create($data)
    {

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
}
