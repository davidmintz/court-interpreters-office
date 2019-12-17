<?php

namespace InterpretersOffice\Admin\Rotation\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
// use Doctrine\ORM\EntityManager;
use InterpretersOffice\Admin\Rotation\Service\TaskRotationService;
use InterpretersOffice\Admin\Rotation\Entity\Task;
use Zend\Mvc\MvcEvent;
/*
access DENIED to user erikadelosrios@msn.com in role manager:
resource InterpretersOffice\Admin\Rotation\Controller\IndexController;
action create-rotation
*/

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

    private $permissions = ['create-rotation'=>false,'create-task'=>false];

    public function onDispatch(MvcEvent $e)
    {
        $sm = $e->getApplication()->getServiceManager();
        //$log = $sm->get('log');
        $role = $sm->get('auth')->getIdentity()->role;
        $resource = RestRotationController::class;
        $acl = $sm->get('acl');
        foreach (array_keys($this->permissions) as $action) {
            $this->permissions[$action] = $acl->isAllowed($role,$resource,$action);
        }
        return parent::onDispatch($e);
    }

    public function viewAction() {

        $id = $this->params()->fromRoute('id');
        $task = $this->service->getTask($id);
        $current = $this->service->getAssignment(date('Y-m-d'),$id);

        return ['task' => $task, 'current'=>$current, 'permissions'=>$this->permissions, ];

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
        $tasks = $this->service->getEntityManager()
            ->getRepository(Task::class)->findAll();
        return ['tasks' => $tasks,'permissions'=>$this->permissions, ];
    }
}
