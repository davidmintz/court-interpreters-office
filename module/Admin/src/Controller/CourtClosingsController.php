<?php

/** module/Admin/src/Controller/CourtClosingsController */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Entity;

use InterpretersOffice\Admin\Form\CourtClosingForm;

/**
 * controller for admin/court-closings
 */
class CourtClosingsController extends AbstractActionController
{

    /**
     * entity manager
     *
     * @var EntityManagerInterface
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {

        $this->objectManager  = $em;
    }
    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $repo = $this->objectManager->getRepository(Entity\CourtClosing::class);
        $year = $this->params()->fromRoute('year');
        if (! $year) {
            $data = $repo->index();
            return new ViewModel(['data'=>$data]);
        } else {
            $data = $repo->list($year);
            return new JsonModel($data);
        }
    }

    /**
     * form
     *
     * @var CourtClosingForm
     */
    protected $form;

    /**
     * gets form
     *
     * @param  string $action either 'create' or 'update'
     * @return CourtClosingForm
     */
    protected function getForm($action)
    {
        if (! $this->form) {
            $this->form = new CourtClosingForm(
                $this->objectManager,['action'=>$action]);
        }
        return $this->form;
    }
    
    /**
     * handles post data
     *
     * @return JsonModel
     */
    protected function post()
    {
        // work in progress
        $params = $this->params()->fromPost();

        return new JsonModel($params);
    }

    /**
     * adds a court closing
     */
    public function addAction()
    {
        $form = $this->getForm('create');
        if ($this->getRequest()->isPost()) {
            return $this->post();
        }
        $view = new ViewModel();
        $view->setTemplate('interpreters-office/admin/court-closings/form');
        $view->form = $form;

        return $view;
    }

    /**
     * edits a court closing
     */
    public function editAction()
    {
        $form = $this->getForm('update');
        if ($this->getRequest()->isPost()) {

        }
        $view = new ViewModel();
        $view->setTemplate('interpreters-office/admin/court-closings/form');
        $view->form = $form;
        $id = $this->params()->fromRoute('id');
        $entity = $this->objectManager->find(Entity\CourtClosing::class,$id);
        if (! $entity) {
            // to do: deal with it
        }
        $form->bind($entity);

        return $view;
    }
}
