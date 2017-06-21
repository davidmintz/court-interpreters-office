<?php

/** module/Admin/src/Controller/PeopleController */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Admin\Form\InterpreterForm;
use InterpretersOffice\Entity;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\View\Model\JsonModel;
use InterpretersOffice\Admin\Form\InterpreterRosterForm;
use Zend\Session\Container as Session;

/**
 * controller for admin/interpreters.
 * @todo DRY out the hydration/processing
 */
class InterpretersController extends AbstractActionController
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
    public function __construct(EntityManagerInterface $entityManager,$vault_enabled)
    {
        
        $this->entityManager = $entityManager;
        $this->vault_enabled = $vault_enabled;
    }

    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {        
        $params = $this->params()->fromRoute();
        $matchedRoute = $this->getEvent()->getRouteMatch();
        $routeName =  $matchedRoute->getMatchedRouteName();
        $isQuery = ( 'interpreters' != $routeName );

        $form = new InterpreterRosterForm(['objectManager' => $this->entityManager]);
        $viewModel = new ViewModel([           
            'title' => 'interpreters',
            'objectManager' => $this->entityManager,
            ] + compact('form','params','isQuery','routeName')
        );        
        $this->initView($viewModel, $params, $isQuery);       
        if ($isQuery) {  
             // i.e., search parameters in URL
            $viewModel->setVariables(['data'=>$this->find($params)]);
        } 
        return $viewModel;       
    }
    
    /**
     * figures out appropriate defaults for interpreter roster search form
     * 
     * @param ViewModel $view
     * @param Array of GET (route) parameters
     * @param boolean $isQuery whether submitting search terms or just arriving
     * 
     * @todo consider making this a method of the form instead
     */
    public function initView(ViewModel $viewModel,Array $params, $isQuery) {
        
        $session = new Session('interpreter_roster');
        if (! $isQuery) {
        // if no search parameters, get previous state from session if possible
            if ($session->params) {
                $viewModel->params = $session->params;
            } 
        } else {
        // save search parameters in session for next time
            $session->params = $params;
            $viewModel->params = $params;
        }
    }
    /**
     * finds interpreters
     * 
     * gets interpreters based on search criteria
     * 
     * @return array
     */
    public function find(Array $params)
    {
        //echo "shit is running!" ;
        
        $repository = $this->entityManager->getRepository('InterpretersOffice\Entity\Interpreter');
        return $repository->search($params,$this->params()->fromQuery('page',1));
        
    }


    /**
     * adds an Interpreter entity to the database.
     */
    public function addAction()
    {
        $viewModel = (new ViewModel())
                ->setTemplate('interpreters-office/admin/interpreters/form.phtml')
                ->setVariables(['title' => 'add an interpreter']);

        $form = new InterpreterForm($this->entityManager,
                [
                    'action' => 'create',
                    'vault_enabled' => $this->vault_enabled
                ]
        );
        $viewModel->form = $form;

        $request = $this->getRequest();
        $entity = new Entity\Interpreter();
        $form->bind($entity);
        if ($request->isPost()) {
            $form->setData($request->getPost());

            $repository = $this->entityManager->getRepository('InterpretersOffice\Entity\Language');
            // manually hydrate, because we could not make that other shit work
            $data = $request->getPost()['interpreter']['interpreter-languages'];
            if (is_array($data))    {
                $this->updateInterpreterLanguages($entity, $data);
                /*
                foreach ($data as $language) {
                    $id = $language['language_id'];
                    $certification = $language['federalCertification'] >= 0 ?
                        (bool) $language['federalCertification'] : null;
                    // or get them all in one shot with a DQL query?
                    $languageEntity = $repository->find($id);
                    $il = (new Entity\InterpreterLanguage($entity, $languageEntity))
                        ->setFederalCertification($certification);
                    $entity->addInterpreterLanguage($il);
                }*/
            }
            
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
        $viewModel = (new ViewModel())
                ->setTemplate('interpreters-office/admin/interpreters/form.phtml')
                ->setVariable('title', 'edit an interpreter');
        $id = $this->params()->fromRoute('id');
        $entity = $this->entityManager->find('InterpretersOffice\Entity\Interpreter', $id);
        if (! $entity) {
            return $viewModel->setVariables(['errorMessage' => "interpreter with id $id not found"]);
        }
        //echo "FUCKING HELLO??????";
        $form = new InterpreterForm($this->entityManager, ['action' => 'update','vault_enabled'=>$this->vault_enabled]);
        $form->bind($entity);
        
        $viewModel->setVariables(['form' => $form, 'id' => $id, 
            // for the re-authentication dialog
            'login_csrf' => (new \Zend\Form\Element\Csrf('login_csrf'))->setAttribute('id','login_csrf')
            ]
        );
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            //echo '<pre>';print_r($request->getPost()['interpreter']['interpreter-languages']);echo '</pre>';
            $this->updateInterpreterLanguages(
                $entity,
                $request->getPost()['interpreter']['interpreter-languages']
            );
            if (! $form->isValid()) {
                //print_r($form->getMessages());
                echo "shit is NOT valid. ";
                return $viewModel;
            }
            $this->entityManager->flush();
            $this->flashMessenger()->addSuccessMessage(sprintf(
                'The interpreter <strong>%s %s</strong> has been updated.',
                $entity->getFirstname(),
                $entity->getLastname()
            ));
            $this->redirect()->toRoute('interpreters');
            //echo "success. NOT redirecting...<a href=\"/admin/interpreters/edit/$id\">again</a> ";
           // echo "<pre>"; var_dump($_POST['interpreter']['interpreter-languages']) ;
           //print_r($form->getMessages()); echo "</pre>";
        } else {
            // not a POST
            if ($this->vault_enabled) {
                $viewModel->obscure_values = true;
            }
        }

        return $viewModel;
    }
    
    public function validatePartialAction()
    {
        $action = $this->params()->fromQuery('action');
        $params = $this->params()->fromPost();//['interpreter'];
        $form = new InterpreterForm($this->entityManager, ['action' => $action,'vault_enabled'=>$this->vault_enabled]);
        $request = $this->getRequest();
        $form->setData($request->getPost());
        $form->setValidationGroup(['interpreter'=>array_keys($params['interpreter'])]);
        if (! $form->isValid()) {
            return new JsonModel(['valid'=>false,'validation_errors'=>$form->getMessages()]);
        }
        return new JsonModel(['valid'=>true]);
    }

    /**
     * manually updates the Interpreter entity's languages.
     *
     * since we were unable to get the Doctrine hydrator to work, for reasons
     * that remain obscure, we have to do it ourself.
     *
     * @param Entity\Interpreter $interpreter
     * @param mixed              $languages   language data POSTed to us
     */
    public function updateInterpreterLanguages(Entity\Interpreter $interpreter, $languages)
    {
        if (! is_array($languages)) {
            // return the interpreter entity in an invalid state (no languages)
            $interpreter->removeInterpreterLanguages($interpreter->getInterpreterLanguages());
            return;
        }
        $repository = $this->entityManager->getRepository('InterpretersOffice\Entity\Language');
        // get $before and $after into two like-structured arrays for comparison
        // i.e.: [ language_id => certification, ]
        $before = [];
        $interpreterLanguages = $interpreter->getInterpreterLanguages();
        //printf("DEBUG: we have %d interpreter-languages...",count($interpreterLanguages));
        foreach ($interpreterLanguages as $il) {
            $array = $il->toArray();
            $before[$array['language_id']] = $array['federalCertification'];
        }
        $after = [];       
        foreach ($languages as $l) {           
            $after[$l['language_id']] = $l['federalCertification'] >= 0 ?
                    (bool)$l['federalCertification'] : null;
        }
        // what has been added?
        $added = array_diff_key($after, $before);
        if (count($added)) {
            foreach ($added as $id => $cert) {
                // to do: snag all the languages in one shot instead?
                $language = $repository->find($id);
                $obj = new Entity\InterpreterLanguage($interpreter, $language);
                $cert = $after[$id];
                $obj->setFederalCertification($cert);
                $interpreter->addInterpreterLanguage($obj);
            }
        }
        // what has been removed?
        $removed = array_diff_key($before, $after);
        if (count($removed)) {
            foreach ($interpreterLanguages as $il) {
                if (key_exists($il->getLanguage()->getId(), $removed)) {
                    $interpreter->removeInterpreterLanguage($il);
                }
            }
        }
        // was any certification field modified?
        foreach ($interpreter->getInterpreterLanguages() as $il) {
            $language = $il->getLanguage();
            $id = $il->getLanguage()->getId();
            $cert = $il->getFederalCertification();
            $submitted_cert = $after[$id];
            if ($cert !== $submitted_cert) {
                $il->setFederalCertification($submitted_cert);
            }
        }
    }
}
 
