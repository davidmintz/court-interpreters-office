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

    use DeletionTrait;

    /**
     * entity manager
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {

        $this->entityManager  = $em;
    }
    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $repo = $this->entityManager->getRepository(Entity\CourtClosing::class);
        $year = $this->params()->fromRoute('year');
        if (! $year) {
            $data = $repo->index();
            return new ViewModel(['data' => $data]);
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
                $this->entityManager,
                ['action' => $action]
            );
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
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();

        return new JsonModel([
            'result' => 'success','entity_id' => $entity->getId()]);
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
        $view->form = $form;

        return $view;
    }

    /**
     * edits a court closing
     */
    public function editAction()
    {
        $id = $this->params()->fromRoute('id');
        $entity = $this->entityManager->find(Entity\CourtClosing::class, $id);
        if (! $entity) {
            // to do: deal with it
        }
        $form = $this->getForm('update');
        $form->bind($entity);
        if ($this->getRequest()->isPost()) {
            return $this->post();
        }

        return  new ViewModel(['form' => $form]);
    }

    /**
     * deletes a court closing
     */
    public function deleteAction()
    {
        $id = $this->params()->fromRoute('id');
        $entity = $this->entityManager->find(Entity\CourtClosing::class, $id);
        if ($entity) {
            $name = $entity->getDate()->format('D d-M-Y');
        } else {
            $name = '';
        }
        $options = [
            'entity' => $entity,'id' => $entity ? $entity->getId() : null,
            'what' => 'court closing on',
            'name' => ''
        ];
        return $this->delete($options);
    }
}
