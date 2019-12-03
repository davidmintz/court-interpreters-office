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
        $filter = $this->service->getSubstitionInputFilter();
        $filter->setData($data);
        if ($filter->isValid()) {

        } else {
            return new JsonModel(['validation_errors'=> $filter->getMessages()]);
        }
        return new JsonModel(['status' => 'valid. nice job']);
    }

    /**
     * gets task assignment
     *
     * mapped to '/admin/rotations/assignments/:date/:id'
     *
     * @return JsonModel
     */
    public function get($id)
    {
        $date = $this->params()->fromRoute('date');
        $data = $this->service->getAssignment($date,(int)$id) ;
        $data['csrf'] = (new Csrf())->getHash();

        return new JsonModel($data);
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
}
