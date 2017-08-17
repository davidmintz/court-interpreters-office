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
use InterpretersOffice\Admin\Form\InterpreterRosterForm;
use Zend\Session\Container as Session;
use Zend\Stdlib\Parameters;

/**
 * controller for admin/interpreters.
 * @todo DRY out the hydration/processing
 * @todo split off the read-only functions from the update/insert
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
    public function __construct(EntityManagerInterface $entityManager, $vault_enabled)
    {

        $this->entityManager = $entityManager;
        $this->vault_enabled = $vault_enabled;
    }

    /**
     * display Interpreter details view
     *
     * to be implemented
     */
    public function viewAction()
    {
        //$id = $this->params()->fromRoute('id');
        return new ViewModel();
    }
    
    public function languageFieldsetAction()
    {
        $id = $this->params()->fromQuery('id');
        $index = $this->params()->fromQuery('index',0);
        if ($id) {
            $language = $this->entityManager->find(Entity\Language::class,$id);
        }
        //$this->getResponse()->getHeaders()->addHeaderLine('content-type','text/plain');
        return (new ViewModel(['language'=>$language,'index'=>$index]))
                ->setTemplate('partials/interpreters/language.phtml')
                ->setTerminal(true);
    }
    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {      
        $autocomplete_term = $this->params()->fromQuery('term');
        if ($autocomplete_term) {
            return $this->autocomplete($autocomplete_term);
        }

        $params = $this->params()->fromRoute();
        $matchedRoute = $this->getEvent()->getRouteMatch();
        $routeName = $matchedRoute->getMatchedRouteName();
        $isQuery = ( 'interpreters' != $routeName );
        $form = new InterpreterRosterForm(['objectManager' => $this->entityManager]);
        $viewModel = new ViewModel([
            'title' => 'interpreters',
            //'objectManager' => $this->entityManager,
            ] + compact('form', 'params', 'isQuery', 'routeName')
        );
        if ('interpreters/find_by_id' == $routeName) {
            $viewModel->interpreter = $this->entityManager->find(
                 Entity\Interpreter::class, $this->params()->fromRoute('id')
            );
        } else {
            if ($isQuery) {
                // i.e., there are search parameters in URL
                $viewModel->results = $this->find($params);
            }
        }
        return $this->initView($viewModel, $params, $isQuery);
    }

    /**
     * returns autocompletion data for name search textfield
     *
     * @param string $term
     * @return JsonModel
     */
    public function autocomplete($term)
    {

        $repository = $this->entityManager->getRepository('InterpretersOffice\Entity\Interpreter');
        return new JsonModel(
            $repository->autocomplete($term)
        );
    }

    /**
     * figures out appropriate defaults for interpreter roster search form
     *
     * @param ViewModel $viewModel
     * @param Array $params GET (route) parameters
     * @param boolean $isQuery whether submitting search terms, or just arriving
     *
     * @todo consider making this a method of the form instead
     */
    public function initView(ViewModel $viewModel, array $params, $isQuery)
    {

        $session = new Session('interpreter_roster');//$session->clear();return;

        if (! $isQuery) {
        // if no search parameters, get previous state from session if possible
            if ($session->params) {
                $viewModel->params = $session->params;
            }
        } else {
            // save search parameters in session for next time

            if (! empty($params['lastname'])) {
                $params['name'] = $params['lastname'];
                if (! empty($params['firstname'])) {
                    $params['name'] .= ", {$params['firstname']}" ;
                }
            }
            $session->params = $merged = array_merge($session->params ?: [], $params);
            $viewModel->params = $merged;
            //var_dump($session->params);
        }
        return $viewModel;
    }

    /**
     * finds interpreters
     *
     * gets interpreters based on search criteria. if we are given an id
     * parameter, find by id
     *
     * @param Array $params interpreter search parameters
     * @return array
     */
    public function find(array $params)
    {
        $repository = $this->entityManager
                ->getRepository(Entity\Interpreter::class);

        return $repository->search($params, $this->params()->fromQuery('page', 1));
    }

    /**
     * adds an Interpreter entity to the database.
     */
    public function addAction()
    {
        $viewModel = (new ViewModel())
            ->setTemplate('interpreters-office/admin/interpreters/form.phtml');
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
            $this->redirect()->toRoute('interpreters');
            //echo "<br>success. NOT redirecting...<a href=\"/admin/interpreters/edit/$id\">again</a> ";
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
            $form = new InterpreterForm($this->entityManager, ['action' => $action,'vault_enabled' => $this->vault_enabled]);
            $request = $this->getRequest();
            $form->setData($request->getPost());
            // temporary
            if (key_exists('interpreter',$params)) {
                $form->setValidationGroup(['interpreter' => array_keys($params['interpreter'])]);
                if (! $form->isValid()) {
                    return new JsonModel(['valid' => false,'validation_errors' => $form->getMessages()['interpreter']]);
                }
            }       
            return new JsonModel(['valid' => true]);
        } catch (\Exception $e) {

            return new JsonModel(['valid' => false, 'error' => $e->getMessage()]);
        }
        
    }
}
