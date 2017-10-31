<?php

/** module/Admin/src/Controller/InterpretersController */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Admin\Form\InterpreterForm;
use InterpretersOffice\Entity;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\View\Model\JsonModel;
use InterpretersOffice\Admin\Form\View\Helper\LanguageElementCollection as
    LanguageCollectionHelper;
use Zend\Stdlib\Parameters;

/**
 * controller for admin/interpreters create|update|delete 
 *
 */
class InterpretersWriteController extends AbstractActionController
{

    /**
     * whether our Vault module is enabled
     * @var boolean
     */
    protected $vault_enabled;

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
     * @param boolean $vault_enabled
     */
    public function __construct(EntityManagerInterface $entityManager, $vault_enabled)
    {

        $this->entityManager = $entityManager;
        $this->vault_enabled = $vault_enabled;
    }

    /**
     * adds an Interpreter entity to the database.
     */
    public function addAction()
    {
        $viewModel = (new ViewModel())
            ->setTemplate('interpreters-office/admin/interpreters/form.phtml');
        $form = new InterpreterForm($this->entityManager,
                [ 'action' => 'create',
                 'vault_enabled' => $this->vault_enabled ]
        );
        $viewModel->form = $form;
        $request = $this->getRequest();
        $entity = new Entity\Interpreter();
        $form->bind($entity);
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (! $form->isValid()) {
                return $viewModel;
            }
            $this->entityManager->persist($entity);
            $this->entityManager->flush();

            $this->flashMessenger()->addSuccessMessage(
              sprintf(
                'The interpreter <strong>%s %s</strong> has been added to the database',
                $entity->getFirstname(),
                $entity->getLastname()
                )
            );
            //echo "success. NOT redirecting. <a href=\"/admin/interpreters/add\">again</a>";
            $this->redirect()->toRoute('interpreters');
        }

        return $viewModel;
    }

    /**
     * updates an Interpreter entity.
     */
    public function editAction()
    {
        /* // very annoying: '201' is considered a 4-digit year
        $value = '04/23/201';
        $validator = new \Zend\Validator\Date([
           'format' => 'm/d/Y',
           'messages' => [\Zend\Validator\Date::INVALID_DATE => 'valid date in MM/DD/YYYY format is required']
        ]);*/
        //var_dump($validator->isValid($value));

        $viewModel = (new ViewModel())
            ->setTemplate('interpreters-office/admin/interpreters/form.phtml');
        $id = $this->params()->fromRoute('id');
        $entity = $this->entityManager->find(Entity\Interpreter::class, $id);        
        if (! $entity) {
            return $viewModel->setVariables(
                   ['errorMessage' => "interpreter with id $id not found"]);
        }
        $values_before = [
            'dob' => $entity->getDob(),
            'ssn' => $entity->getSsn(),
        ];
        $form = new InterpreterForm($this->entityManager, 
                ['action' => 'update','vault_enabled' => $this->vault_enabled]);
        $form->bind($entity);
        $viewModel->setVariables(['form' => $form, 'id' => $id,
            // for the re-authentication dialog
            'login_csrf' => (new \Zend\Form\Element\Csrf('login_csrf'))
                        ->setAttribute('id', 'login_csrf')
            ]
        );
        $request = $this->getRequest();
        if ($request->isPost()) {
            $input = $request->getPost();
            $form->setData($input);
             
            //printf('<pre>%s</pre>',print_r($data->get('event'),true)); return false;
            //$this->preValidate($input,$form);
            //$form->setData($input);
            printf('<pre>%s</pre>',print_r($input->get('interpreter'),true)); 
            if (! $form->isValid()) {
                
                // whether the encrypted fields should be obscured (again) 
                // or not depends on whether they changed them                
                $viewModel->obscure_values = 
                  ! $this->getEncryptedFieldsWereModified($values_before,$input);                
                return $viewModel;
            }
            $this->entityManager->flush();
            $this->flashMessenger()->addSuccessMessage(sprintf(
                'The interpreter <strong>%s %s</strong> has been updated.',
                $entity->getFirstname(), $entity->getLastname()
            ));
            //$this->redirect()->toRoute('interpreters');
             echo "<br>success. NOT redirecting...
            // <a href=\"/admin/interpreters/edit/$id\">do it again</a> ";
        } else {    // not a POST
            if ($this->vault_enabled) {
                $viewModel->obscure_values = true;
            }           
        }

        return $viewModel;
    }
    /**
     * were the dob and ssn fields modified?
     * @param Array $values_before the dob and ssn used when form was loaded
     * @param $input \Zend\Stdlib\Parameters
     * @return boolean
     */
    public function getEncryptedFieldsWereModified(Array $values_before,
            Parameters $input)            
    {
        return $input->get('dob') != $values_before['dob']
                or 
                $input->get('ssn') != $values_before['ssn'];
    }
    
    /**
     * validates part of the Interpreter form
     *
     * invoked when the user changes tabs on the Interpreter form
     *
     * @todo DO NOT run if not xhr, check presence of 'interpreters' index
     * @return JsonModel
     */
    public function validatePartialAction()
    {

        if (! $this->getRequest()->isXmlHttpRequest()) {
            $this->redirect()->toRoute('interpreters');
        }
        try {
            $action = $this->params()->fromQuery('action');
            $params = $this->params()->fromPost();//['interpreter'];
            $form = new InterpreterForm($this->entityManager,
                 ['action' => $action,'vault_enabled' => $this->vault_enabled]);
            $request = $this->getRequest();
            $form->setData($request->getPost());
            if (key_exists('interpreter',$params)) {
                $form->setValidationGroup(
                        ['interpreter' => array_keys($params['interpreter'])]);
                if (! $form->isValid()) {
                    return new JsonModel([
                        'valid' => false,
                        'validation_errors' => 
                            $form->getMessages()['interpreter']
                    ]);
                }
            }       
            return new JsonModel(['valid' => true]);
        } catch (\Exception $e) {
            return new JsonModel(['valid' => false, 'error' => $e->getMessage()]);
        }
        
    }
    
    /**
     * creates form view helper for interpreter-language markup
     * 
     * @return LanguageCollectionHelper
     */
    protected function getLanguageCollectionHelper()
    {
        $helper = new LanguageCollectionHelper();
         $container = $this->getEvent()
            ->getApplication()->getServiceManager();
        $renderer = $container->get('ViewRenderer');
        $helper->setView($renderer);
        
        return $helper;
    }
    
     /**
     * renders HTML fragment for an interpreter language
     * 
     * @return Zend\Http\PhpEnvironment\Response
     */
    public function languageFieldsetAction()
    {
        $id = $this->params()->fromQuery('id');
        $index = $this->params()->fromQuery('index',0);
        $language = $this->entityManager->find(Entity\Language::class,$id);
        if (! $language) {
            return $this->getResponse()->setContent("<br>WTF??");
        }
        $helper = $this->getLanguageCollectionHelper();
        $content = $helper->fromArray(compact('language','index'));
        
        return $this->getResponse()->setContent($content);        
    }
}
