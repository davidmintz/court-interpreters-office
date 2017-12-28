<?php

/** module/Admin/src/Controller/DefendantsController */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
//use Zend\View\Model\JsonModel;
//use InterpretersOffice\Form\PersonForm;
use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Entity;

use InterpretersOffice\Admin\Form\DefendantForm;

/**
 * controller for admin/defendants.
 */
class DefendantsController extends AbstractActionController
{
    /**
     * entity manager.
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        return new ViewModel(['title' => 'defendants']);
    }

    /**
     * adds a Person entity to the database.
     */
    public function addAction()
    {
        $viewModel = (new ViewModel())
                ->setTemplate('interpreters-office/admin/defendants/form.phtml');
        $form = new DefendantForm($this->entityManager, ['action' => 'create']);
        $viewModel->setVariables(['form' => $form, 'title' => 'add a defendant name']);
        $request = $this->getRequest();
        $entity = new Entity\DefendantName();
        $form->bind($entity);
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (! $form->isValid()) {
                return $viewModel;
            }
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
            /*
            $this->flashMessenger()->addSuccessMessage(
                sprintf(
                    'The person <strong>%s %s</strong> has been added to the database',
                    $entity->getFirstname(),
                    $entity->getLastname()
                )
            );
            $this->redirect()->toRoute('people');
             * 
             */
        }

        return $viewModel;
    }

    /**
     * updates a defendant entity.
     */
    public function editAction()
    {
        $viewModel = (new ViewModel())
                ->setTemplate('interpreters-office/admin/defendants/form.phtml')
                ->setVariable('title', 'edit a defendant name');
        $id = $this->params()->fromRoute('id');

        return $viewModel;
    }

}
