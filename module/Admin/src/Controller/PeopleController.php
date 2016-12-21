<?php

/** module/Admin/src/Controller/PeopleController */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use InterpretersOffice\Form\PersonForm;

use Doctrine\ORM\EntityManagerInterface;

use InterpretersOffice\Entity;

/**
 * controller for admin/people.
 */
class PeopleController extends AbstractActionController
{
    
    /**
     * entity manager.
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;
    
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
        return new ViewModel();
    }
    
    public function addAction()
    {
        $viewModel = (new ViewModel())
                ->setTemplate('interpreters-office/admin/people/form.phtml');
        $form = new PersonForm($this->entityManager);
        $viewModel->setVariables(['form'=>$form,'title'=>'add a person']);
        $request = $this->getRequest();
        $entity = new Entity\Person();
        $form->bind($entity);
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (!$form->isValid()) {
                return $viewModel;
            } 
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
            $this->flashMessenger()->addSuccessMessage(
                  sprintf('The person %s %s has been added to the database',
                  $entity->getFirstname(),$entity->getLastname()));
            $this->redirect()->toRoute('people');
        }
        return $viewModel;
    }
}
