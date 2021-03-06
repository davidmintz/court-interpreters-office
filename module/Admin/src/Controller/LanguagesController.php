<?php
/**
 * module/Admin/src/Controller/LanguagesController.php.
 */

namespace InterpretersOffice\Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
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
    use DeletionTrait;

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
        $repository = $this->entityManager->getRepository(Language::class);
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
        $repo = $this->entityManager->getRepository(Language::class);
        $entity = $repo->findOneBy(['id' => $id]);
        if (! $entity) {
            return $this->getFormViewModel(['errorMessage' => "language with id $id not found"]);
        }
        $form = $this->getForm(
            Language::class,
            ['object' => $entity, 'action' => 'update',]
        )
               ->bind($entity);
        $viewModel = $this->getFormViewModel(
            [ 'form' => $form, 'has_related_entities' => $repo->hasRelatedEntities($id),
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
                  ->addSuccessMessage(
                      "The language <strong>$entity</strong> has been updated."
                  );
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
            $what = 'language';
            $entity = $this->entityManager->find(Language::class, $id);

            return $this->delete(compact('entity', 'id', 'name', 'what'));
        }
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
        return new ViewModel($data);
    }
}
