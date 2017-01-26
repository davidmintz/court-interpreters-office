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
     * @todo fix. it's broken. add-language is not working.
     */
    public function addAction()
    {

        $viewModel = (new ViewModel())
                ->setTemplate('interpreters-office/admin/interpreters/form.phtml')
                ->setVariables(['title' => 'add an interpreter']);
        
        $form = new InterpreterForm($this->entityManager, ['action' => 'create']);
        $viewModel->form = $form;
        
        $request = $this->getRequest();
        $entity = new Entity\Interpreter();
        $form->bind($entity);
        if ($request->isPost()) {
            echo "SHIT IS POSTed ...";
           
            //$_POST['interpreter']['interpreterLanguages'][0] = ['language'=> 24];
            $form->setData($request->getPost());
            if (!$form->isValid()) {  
                echo "SHIT IS NOT VALID ...";//exit();
                echo "<pre>\$_POST['interpreter']['interpreter-languages']:<br>"; 
                var_dump($_POST['interpreter']['interpreter-languages']) ;
                echo "error messages:\n";
                print_r($form->getMessages());
                echo "</pre>";      
                return $viewModel;
            }
            try {
              echo "SHIT IS VALID ...";//exit();
              $this->hydrate($entity,$request->getPost()['interpreter']);
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
            echo "success. NOT redirecting. <a href=\"/admin/interpreters/add\">again</a>";
            //$this->redirect()->toRoute('interpreters');
        }
        return $viewModel;
    }
    
    /**
     * manually deals with hydration of the Interpreter's languages
     * 
     * @param \InterpretersOffice\Entity\Interpreter $entity
     * @param array $data
     */
    protected function hydrate(Entity\Interpreter $entity,Array $data)
    {
        
        //echo "DATA:<pre>"; print_r($data['interpreter-languages']); echo "</pre>";
        $repository = $this->entityManager->getRepository('InterpretersOffice\Entity\Language');
        /** @todo manually figure out what needs deleting so as to avoid duplicate entry exception */
        
       // this doesn't get it done.
       $entity->removeInterpreterLanguages($entity->getInterpreterLanguages());
        
       $action = $this->params()->fromRoute('action');
       if ('edit' == $action) {
           echo "<br>this is an update involving {$entity->getId()}...";
       }
       $after = [];
       $before = $this->interpreterLanguages;
       foreach ($data['interpreter-languages'] as $index => $language_data) {  
           $after[$language_data['language_id']] = [
               'federalCertification' => $language_data['federalCertification'],
           ];
           
           // $language = $repository->find($language_data['language_id']);
            /** @todo the certification field */
          //  $entity->addInterpreterLanguage(new Entity\InterpreterLanguage($entity,$language));            
       }
       $modified = $before != $after;
       echo "<pre>before: "; print_r($before); echo "after: "; print_r($after); echo "</pre>";
       // did they add or remove, or just update a certification field?
       //$modified_certification = $modified && array_keys($before) == array_keys($after);
       if ($modified) {
           echo "yes, modified...";
           $to_be_removed = array_diff_key($before,$after);
           $to_be_added   = array_diff_key($after,$before);
           printf("%d to remove, %d to add<br>",count($to_be_removed),count($to_be_added));
           // to be continued: figure out how to handle updated federalCertification
       } else {
           echo "NOT modified? ";
       }
      
       
    }
    /**
     * (temporary) instance variable for keeping interpreter-language data
     * pre-update. need to move this to a service or something.
     * @var array
     */
    protected $interpreterLanguages = [];
    
    /**
     * updates an Interpreter entity.
     */
    public function editAction()
    {
        
        $viewModel = (new ViewModel())
                ->setTemplate('interpreters-office/admin/interpreters/form.phtml')
                ->setVariable('title', 'edit an interpreter');
        $id = $this->params()->fromRoute('id');
        $entity = $this->entityManager->find('InterpretersOffice\Entity\Interpreter', $id);
        if (!$entity) {
            return $viewModel->setVariables(['errorMessage' => "interpreter with id $id not found"]);
        }
        foreach ($entity->getInterpreterLanguages() as $object) {
            list($lang_id,$data) = each($object->toArray());            
            $this->interpreterLanguages[$lang_id] = $data;
        }
        
        
        $form = new InterpreterForm($this->entityManager, ['action' => 'update']);
        $form->bind($entity);
        $viewModel->setVariables(['form' => $form, 'id' => $id ]);
        $request = $this->getRequest();
        if ($request->isPost())
        {
            $form->setData($request->getPost());
            $this->hydrate($entity,$request->getPost()['interpreter']);
            if (!$form->isValid()) {
                echo "shit not valid?...";
                echo "<pre>"; var_dump($_POST['interpreter']['interpreter-languages']) ;
                print_r($form->getMessages());
                echo "</pre>"; 
                
                //print_r($form->getData());
                return $viewModel;
            }
            
           // $this->entityManager->flush();
            $this->flashMessenger()
                  ->addSuccessMessage(sprintf(
                      'The interpreter <strong>%s %s</strong> has been updated.',
                      $entity->getFirstname(),
                      $entity->getLastname()
                  ));
            echo "success. NOT redirecting... ";
            echo "<pre>"; var_dump($_POST['interpreter']['interpreterLanguages']) ;
                print_r($form->getMessages());
                echo "</pre>"; 
                
            ////entity:<pre>";
            //\Doctrine\Common\Util\Debug::dump($entity); echo "</pre>";
            //$this->redirect()->toRoute('interpreters');
        } else { //echo "loaded:<pre> "; \Doctrine\Common\Util\Debug::dump($entity);echo "</pre>";
       }

        return $viewModel;
    }
}
