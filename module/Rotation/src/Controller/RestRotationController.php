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
     * creates a new Rotation for a Task
     *
     * @param  Array $data
     * @return JsonModel
     */
    public function create($data)
    {
        $inputFilter = $this->service->getRotationInputFilter();
        $data['countable'] = $data['members'] ?? null;
        $inputFilter->setData($data);
        $valid = $inputFilter->isValid();
        if (! $valid) {
            return new JsonModel(
                [
                    'validation_errors' => $inputFilter->getMessages(),
                    'valid' => false,
                ]
            );
        }
        return new JsonModel([
            'status'=>'OK',
            'valid' => $valid,
            'info' => 'not yet implemented',
            'data' => $inputFilter->getValues(),
        ]);
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
}
