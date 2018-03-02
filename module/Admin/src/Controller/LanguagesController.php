<?php
/**
 * module/Admin/src/Controller/LanguagesController.php.
 */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use InterpretersOffice\Form\Factory\FormFactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Entity\Language;
use InterpretersOffice\Form\AnnotatedFormCreationTrait;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;

/**
 *  LanguagesController, for managing languages.
 */
class LanguagesController extends AbstractActionController
{
    use AnnotatedFormCreationTrait;

    /**
     * FormFactoryInterface.
     *
     * for instantiating the Form
     *
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * entity manager.
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * short name of the controller.
     *
     * @var string
     */
    protected $name = 'languages';

    /**
     * constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param FormFactoryInterface   $formFactory
     * @param string                 $shortName     this controller's short name/type of entity
     *
     * @see InterpretersOffice\Controller\Factory\SimpleEntityControllerFactory
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory,
        $shortName = null
    ) {
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        //$this->name = $shortName;
    }

    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        /* // this gets them all, ordered
        $languages = $this->$this->entityManager
                ->getRepository('InterpretersOffice\Entity\Language')
                ->findBy([],['name'=>'ASC']);
        */
        $page = $this->params()->fromQuery('page', 1);
        $repository = $this->entityManager->getRepository('InterpretersOffice\Entity\Language');
        $languages = $repository->findAllWithPagination($page);

        return new ViewModel(['languages' => $languages, 'title' => 'languages']);
    }

    /**
     * updates a language.
     *
     * @return ViewModel
     */
    public function editAction()
    {
        $id = $this->params()->fromRoute('id');
        if (! $id) {
            return $this->getFormViewModel(['errorMessage' => 'invalid or missing id parameter']);
        }
        $entity = $this->entityManager->find(Language::class, $id);
        if (! $entity) {
            return $this->getFormViewModel(['errorMessage' => "language with id $id not found"]);
        }
        $form = $this->getForm(
            Language::class,
            ['object' => $entity, 'action' => 'update',]
        )
               ->bind($entity);
        $viewModel = $this->getFormViewModel(
            [ 'form' => $form, 'has_related_entities' => $entity->hasRelatedEntities(),
            'title' => 'edit a language', 'id' => $id ]
        );
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (! $form->isValid()) {
                return $viewModel;
            }
            $this->entityManager->flush();
            $this->flashMessenger()
                  ->addSuccessMessage("The language <strong>$entity</strong> has been updated.");
            $this->redirect()->toRoute('languages');
        }

        return $viewModel;
    }

    /**
     * deletes a language.
     * @todo log it
     * @return JsonModel
     */
    public function deleteAction()
    {

        $request = $this->getRequest();

        if ($request->isPost()) {
            $id = $this->params()->fromRoute('id');
            $name = $this->params()->fromPost('name');
            $entity = $this->entityManager->find(Language::class, $id);
            if ($entity) {
                //$thing = $this->getEvent()->getApplication()->getServiceManager()->get('ViewHelperManager');
                //$helper = $thing->get("url"); echo $helper('languages');
                try {
                    $this->entityManager->remove($entity);
                    $this->entityManager->flush();
                    $this->flashMessenger()
                          ->addSuccessMessage("The language <strong>$entity</strong> has been deleted.");
                    $result = 'success';
                    $error = [];
                } catch (ForeignKeyConstraintViolationException $e) {
                    $result = 'error';
                    $error = [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                    ];
                    $this->flashMessenger()
                          ->addWarningMessage(
                              "The language <strong>$name</strong> could not be deleted because it has related database records."
                          );
                }
            } else {
                $result = 'error';
                $error = ['message' => "language id $id not found"];
                $this->flashMessenger()
                      ->addWarningMessage("The language <strong>$name</strong> was not found.");
            }
        }

        return new JsonModel(compact('result', 'error'));
    }

    /**
     * adds a new language.
     *
     * @return ViewModel
     */
    public function addAction()
    {
        $language = new Language();

        $form = $this->getForm(Language::class, ['object' => $language, 'action' => 'create'])
                ->bind($language);
        $viewModel = $this->getFormViewModel(
            ['form' => $form, 'title' => 'add a language']
        );
        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (! $form->isValid()) {
                return $viewModel;
            }

            $this->entityManager->persist($language);
            $this->entityManager->flush();
            $this->flashMessenger()
                  ->addSuccessMessage("The language <strong>$language</strong> has been added.");
            $this->redirect()->toRoute('languages');
        }

        return $viewModel;
    }
    /**
     * get the viewModel.
     *
     * @param array $data
     *
     * @return ViewModel
     */
    protected function getFormViewModel(array $data)
    {
        return (new ViewModel($data))
                ->setTemplate("interpreters-office/admin/{$this->name}/form.phtml");
    }
}
