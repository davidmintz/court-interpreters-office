<?php
/** module/Rotation/src/Controller/RestRotationController.php */

namespace InterpretersOffice\Admin\Rotation\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Validator\Csrf;
use InterpretersOffice\Admin\Rotation\Service\TaskRotationService;
use InterpretersOffice\Admin\Rotation\Entity\Task;

/**
 * RESTful controller for Tasks/Rotations
 */
class RestTaskController extends AbstractRestfulController
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
     * creates a new Task
     *
     * @param  Array $data
     * @return JsonModel
     */
    public function create($data)
    {

        $inputFilter = $this->service->getTaskInputFilter();
        
        $result = ['status'=> "not yet implemented"];
        if (isset($result['status']) && 'success' == $result['status']) {
            $this->flashMessenger()->addSuccessMessage('A new task has been created.');
        }
        return new JsonModel($result);
    }


    /**
     *
     * @return JsonModel
     */
    public function get($id)
    {
        return new JsonModel(['info'=> 'GET is not yet implemented']);
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
        return new JsonModel(['status' => 'UPDATE has yet to be implemented']);
    }

    /**
     * deletion
     *
     * @param  $id  entity id
     *
     * @return JsonModel
     */

    public function delete($id)
    {
        return new JsonModel(['info' => 'DELETE has yet to be implemented']);
    }
}
