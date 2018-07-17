<?php /** module/Admin/src/Controller/InterpretersController */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Admin\Form\InterpreterForm;
use InterpretersOffice\Entity;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\View\Model\JsonModel;
use InterpretersOffice\Admin\Form\View\Helper\LanguageElementCollection as
    LanguageCollectionHelper;
use Zend\Stdlib\Parameters;
use SDNY\Vault\Service\VaultException;

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
     * path to form template
     *
     * @var string
     */
    protected $form_template = 'interpreters-office/admin/interpreters/form.phtml';

    /**
     * ViewModel
     *
     * @var ViewModel
     */
    protected $viewModel;

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
        $this->viewModel = new ViewModel(['vault_enabled' => $vault_enabled]);
    }

    /**
     * on dispatch event handler
     *
     * to be continued...
     *
     * @param  MvcEvent $e
     * @return mixed
     */
    public function onDispatch(MvcEvent $e)
    {
        if ($this->vault_enabled) {
            // ping the vault to make sure it's reachable
            // and if not, set an error message in the view
        }
        return parent::onDispatch($e);
    }
    /**
     * adds an Interpreter entity to the database.
     */
    public function addAction()
    {
        $viewModel = $this->viewModel
            ->setTemplate($this->form_template);
        $form = new InterpreterForm(
            $this->entityManager,
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
                //printf("<pre>%s</pre>",print_r($form->getMessages(),true));
                return $viewModel;
            }
            try {
                $this->entityManager->persist($entity);
                $this->entityManager->flush();
            } catch (VaultException $e) {
                $viewModel->vault_error = $e->getMessage();
                return $viewModel;
            }
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

        $viewModel = $this->viewModel
            ->setTemplate($this->form_template);
        $id = $this->params()->fromRoute('id');
        $repo = $this->entityManager->getRepository(Entity\Interpreter::class);
        $entity = $repo->find($id);
        if (! $entity) {
            return $viewModel->setVariables(
                ['errorMessage' => "interpreter with id $id not found"]
            );
        }

        $values_before = [
            'dob' => $entity->getDob(),
            'ssn' => $entity->getSsn(),
        ];
        /** @var \Zend\Form\Form $form */
        $form = new InterpreterForm(
            $this->entityManager,
            ['action' => 'update','vault_enabled' => $this->vault_enabled]
        );
        $form->bind($entity);
        $has_related_entities = $repo->hasRelatedEntities($entity);
        $viewModel->setVariables(['form' => $form, 'id' => $id,
            'has_related_entities' => $repo->hasRelatedEntities($entity),
            // for the re-authentication dialog
            'login_csrf' => (new \Zend\Form\Element\Csrf('login_csrf'))
                        ->setAttribute('id', 'login_csrf')
            ]);
        if ($has_related_entities) {
            $form->getInputFilter()->get('interpreter')->get('hat')
                ->setRequired(false);
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $input = $request->getPost();
            $form->setData($input);
            if (! $form->isValid()) {
                // whether the encrypted fields should be obscured (again)
                // or not depends on whether they changed them
                $viewModel->obscure_values =
                  ! $this->getEncryptedFieldsWereModified($values_before, $input);
                return $viewModel;
            }
            try {

                $this->entityManager->flush();
            } catch (VaultException $e) {
                $viewModel->vault_error = $e->getMessage();
                return $viewModel;
            }

            $this->flashMessenger()->addSuccessMessage(sprintf(
                'The interpreter <strong>%s %s</strong> has been updated.',
                $entity->getFirstname(),
                $entity->getLastname()
            ));
            $this->redirect()->toRoute('interpreters');
            // echo "<br>success. NOT redirecting...
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
    public function getEncryptedFieldsWereModified(
        array $values_before,
        Parameters $input
    ) {
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
            $form = new InterpreterForm(
                $this->entityManager,
                ['action' => $action,'vault_enabled' => $this->vault_enabled]
            );
            $request = $this->getRequest();
            $form->setData($request->getPost());
            if (key_exists('interpreter', $params)) {
                $form->setValidationGroup(
                    ['interpreter' => array_keys($params['interpreter'])]
                );
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
        $index = $this->params()->fromQuery('index', 0);
        $language = $this->entityManager->find(Entity\Language::class, $id);
        if (! $language) {
            return $this->getResponse()->setContent("<br>WTF??");
        }
        $helper = $this->getLanguageCollectionHelper();
        $content = $helper->fromArray(compact('language', 'index'));

        return $this->getResponse()->setContent($content);
    }
}
