<?php
/** module/Rotation/src/Controller/RestRotationController.php */

namespace InterpretersOffice\Admin\Rotation\Controller;

use Laminas\Mvc\Controller\AbstractRestfulController;
use Laminas\View\Model\JsonModel;
use Laminas\Validator\Csrf;
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
     * creates a new Rotation for a Task
     *
     * @param  Array $data
     * @return JsonModel
     */
    public function create($data)
    {

        $result = $this->service->createRotation($data);
        if (isset($result['status']) && 'success' == $result['status']) {
            $this->flashMessenger()->addSuccessMessage('A new rotation has been created.');
        }
        return new JsonModel($result);
    }

    /**
     * creates a new task Substitution
     *
     * @param  Array $data
     * @return JsonModel
     */
    public function createSubstitutionAction()
    {
        $data = $this->params()->fromPost();
        $filter = $this->service->getSubstitutionInputFilter();
        $filter->setData($data);
        if ($filter->isValid()) {
            $result = $this->service->createSubstitution($filter->getValues());
            return new JsonModel($result);
        } else {
            return new JsonModel(['validation_errors'=> $filter->getMessages()]);
        }
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
