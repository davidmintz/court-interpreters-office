<?php

/** module/Admin/src/Controller/PeopleController */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\EntityManagerInterface;

use InterpretersOffice\Admin\Form\InterpreterForm;

use InterpretersOffice\Entity;

/**
 * controller for admin/interpreters.
 */
class InterpretersController extends AbstractActionController
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
        //echo "shit is working"; return false;
        return new ViewModel(['title' => 'interpreters']);
    }

    /**
     * adds a Person entity to the database.
     */
    public function addAction()
    {
        


        $viewModel = (new ViewModel())
                ->setTemplate('interpreters-office/admin/interpreters/form.phtml')
                ->setVariables(['title' => 'add an interpreter']);
        
        $form = new InterpreterForm($this->entityManager, ['action' => 'create']);
        
         

        $viewModel->form = $form;
        //return $viewModel->setVariables(['form' => $form, ]);

        
        $request = $this->getRequest();
        $entity = new Entity\Interpreter();
        $form->bind($entity);
        if ($request->isPost()) {
            //$_POST['interpreter']['interpreterLanguages'][0] = ['language'=> 24];
            $form->setData($request->getPost());
            if (!$form->isValid()) {            
                return $viewModel;
            }
            // this is so not working right now.
            
            //printf('<pre>%s</pre>',  print_r($this->params()->fromPost('interpreter')['interpreterLanguages'],true));
            //return $viewModel;
            try {
            
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
            } catch (\Exception $e) {
            echo $e->getMessage();
            printf('<pre>%s</pre>',$e->getTraceAsString());
            return $viewModel;
          }
            $this->flashMessenger()->addSuccessMessage(
                sprintf(
                    'The interpreter <strong>%s %s</strong> has been added to the database',
                    $entity->getFirstname(),
                    $entity->getLastname()
                )
            );
            $this->redirect()->toRoute('interpreters');

        }

        return $viewModel;
    }

    /**
     * updates a Person entity.
     */
    public function editAction()
    {
        
        //echo "not yet implemented"; return false;

        $viewModel = (new ViewModel())
                ->setTemplate('interpreters-office/admin/interpreters/form.phtml')
                ->setVariable('title', 'edit an interpreter');
        $id = $this->params()->fromRoute('id');

        $entity = $this->entityManager->find('InterpretersOffice\Entity\Interpreter', $id);
        if (!$entity) {
            return $viewModel->setVariables(['errorMessage' => "interpreter with id $id not found"]);
        }
        $form = new InterpreterForm($this->entityManager, ['action' => 'update']);
        $form->bind($entity);
        $viewModel->setVariables(['form' => $form, 'id' => $id ]);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (!$form->isValid()) {
                return $viewModel;
            }
            $this->entityManager->flush();
            $this->flashMessenger()
                  ->addSuccessMessage(sprintf(
                      'The interpreter <strong>%s %s</strong> has been updated.',
                      $entity->getFirstname(),
                      $entity->getLastname()
                  ));
            echo "NOT redirecting.";
            //$this->redirect()->toRoute('interpreters');
        }

        return $viewModel;
    }
}
