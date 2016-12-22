<?php
/**
 * module/Admin/src/Controller/JudgesController.php
 */

namespace InterpretersOffice\Admin\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use InterpretersOffice\Admin\Form\JudgeForm;

use Doctrine\ORM\EntityManagerInterface;

use InterpretersOffice\Entity;

/**
 * JudgesController
 * 
 */
class JudgesController extends AbstractActionController

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
    
    /**
     * add a new Judge
     */
    public function addAction()
    {
        $viewModel = (new ViewModel())->setTemplate('interpreters-office/admin/judges/form.phtml');
        $form = new JudgeForm($this->entityManager);
        $viewModel->setVariables(
            ['title' => 'add a judge','form'=> $form]
        );
        
        $request = $this->getRequest();
        $hatRepository = $this->entityManager->getRepository('InterpretersOffice\Entity\Hat');
        $hat = $hatRepository->findOneBy(['name'=>'Judge']);
        $entity = new Entity\Judge($hat);
        $form->bind($entity);
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (!$form->isValid()) {
                echo "not valid.";
                print_r($form->getMessages());
                return $viewModel;
            } 
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
            $this->flashMessenger()->addSuccessMessage(
                  sprintf('Judge %s %s, %s has been added to the database',
                    $entity->getFirstname(),
                    $entity->getLastname(),
                    (string)$entity->getFlavor()
                ));
            $this->redirect()->toRoute('judges');
        }
        return $viewModel;
        
    }
}
