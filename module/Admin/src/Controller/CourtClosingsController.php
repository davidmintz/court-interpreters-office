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
        $response = $this->getResponse();
        //printf("<pre>%s</pre>",print_r(get_class_methods($response),true));
        //return false;
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
        $data = $this->getRequest()->getPost();
        $form = $this->form;
        $form->setData($data);
        if (! $form->isValid()) {
            return new JsonModel(
                ['validation_errors' => $form->getMessages() ]
            );
        }
        $entity = $form->getObject();
        // not happy about having to do this here, when the Doctrine hydrator
        // does just fine for us elsewhere. WTF?
        $date = new \DateTime($data->get('date'));
        $entity->setDate($date);
        if (! $entity->getId()) {
            $this->objectManager->persist($entity);
        }
        try {            
            $this->objectManager->flush();
        } catch (\Exception $e) {
            $this->getResponse()->setStatusCode(500);
            return new JsonModel([
                'result'=>'error','message' => $e->getMessage(),
                'entity_id'=>$entity->getId()]
            );
        }
        return new JsonModel([
            'result'=>'success','entity_id'=>$entity->getId()]
        );
    }

    /**
     * adds a court closing
     */
    public function addAction()
    {
        $form = $this->getForm('create');
        $entity = new Entity\CourtClosing();
        $form->bind($entity);
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
        $id = $this->params()->fromRoute('id');
        $entity = $this->objectManager->find(Entity\CourtClosing::class,$id);
        if (! $entity) {
            // to do: deal with it
        }
        $form = $this->getForm('update');
        $form->bind($entity);
        if ($this->getRequest()->isPost()) {
            return $this->post();
        }
        $view = new ViewModel();
        $view->setTemplate('interpreters-office/admin/court-closings/form');
        $view->form = $form;

        return $view;
    }
}
