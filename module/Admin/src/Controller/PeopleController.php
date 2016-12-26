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
        return new ViewModel(['title' => 'people']);
    }
    
    public function addAction()
    {
        $viewModel = (new ViewModel())
                ->setTemplate('interpreters-office/admin/people/form.phtml');
        $form = new PersonForm($this->entityManager,['action' => 'create']);
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
                  sprintf('The person <strong>%s %s</strong> has been added to the database',
                  $entity->getFirstname(),$entity->getLastname()));
            $this->redirect()->toRoute('people');
        }
        return $viewModel;
    }
    
    public function editAction()
    {
        $viewModel = (new ViewModel())
                ->setTemplate('interpreters-office/admin/people/form.phtml')
                ->setVariable('title','edit a person');
        $id = $this->params()->fromRoute('id');
        if (!$id) { // get rid of this, since it will otherwise be 404?
            return $viewModel->setVariables(['errorMessage' => 'invalid or missing id parameter']);
        }
        $entity = $this->entityManager->find('InterpretersOffice\Entity\Person', $id);
        if (!$entity) {
            return $viewModel->setVariables(['errorMessage' => "person with id $id not found"]);
        } else {
            // judges and interpreters are special cases
            if (is_subclass_of($entity,Entity\Person::class)) {
                return $this->redirectToFormFor($entity);
            }
            $viewModel->id = $id;
        }
        $form = new PersonForm($this->entityManager,['action' => 'update']);
        $form->bind($entity);
        $viewModel->form = $form;

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (!$form->isValid()) {
                return $viewModel;
            }
            $this->entityManager->flush();
            $this->flashMessenger()
                  ->addSuccessMessage(sprintf("The person <strong>%s %s</strong> has been updated.",
                    $entity->getFirstname(),$entity->getLastname()      
                    ));
            $this->redirect()->toRoute('people');
        }

        return $viewModel;
    }
    
    public function redirectToFormFor(Entity\Person $entity) {
        
        $class = get_class($entity);
        $base  = substr($class,strrpos($class,'\\')+1);
        $route = strtolower($base).'s/edit';
        $this->redirect()->toRoute($route,['id'=>$entity->getId()]);
        
    }
}
