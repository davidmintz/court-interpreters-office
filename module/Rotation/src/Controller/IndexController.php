<?php

namespace InterpretersOffice\Admin\Rotation\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
// use Doctrine\ORM\EntityManager;
use InterpretersOffice\Admin\Rotation\Service\TaskRotationService;
use InterpretersOffice\Admin\Rotation\Entity\Task;


class IndexController extends AbstractActionController
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

    public function viewAction() {

        $id = $this->params()->fromRoute('id');
        $task = $this->service->getTask($id);
        $current = $this->service->getAssignment(date('Y-m-d'),$id);

        return ['task' => $task, 'current'=>$current ];

    }
    public function createRotationAction()
    {
        $tasks = $this->service->getEntityManager()->getRepository(Task::class)->findAll();
        return ['tasks' => $tasks,'task_id'=>$this->params()->fromRoute('task_id') ];
    }

    public function createTaskAction()
    {
    //    return false;
    }

    /**
     * entry point for rotating task admin
     */
    public function indexAction()
    {
        $tasks = $this->service->getEntityManager()->getRepository(Task::class)->findAll();
        return ['tasks' => $tasks ];
    }
}
