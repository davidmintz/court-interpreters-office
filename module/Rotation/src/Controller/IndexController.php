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

        return ['task' => $task ];

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
