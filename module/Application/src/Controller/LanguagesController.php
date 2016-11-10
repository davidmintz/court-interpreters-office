<?php
/**
 * module/Application/src/Controller/LanguagesController.php.
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
//use Zend\ServiceManager\AbstractPluginManager;
use Application\Form\Factory\FormFactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Application\Entity\Language;
use Application\Form\AnnotatedFormCreationTrait;

/**
 *  LanguagesController, for managing languages.
 */
class LanguagesController extends AbstractActionController
{
    use AnnotatedFormCreationTrait;

    /**
     * FormFactoryInterface
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
    protected $name;

    /**
     * constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param AbstractPluginManager  $formElementManager
     * @param string                 $shortName          this controller's short name/type of entity
     *
     * @see Application\Controller\Factory\SimpleEntityControllerFactory
     */
    public function __construct(
            EntityManagerInterface $entityManager, 
            FormFactoryInterface $formFactory, $shortName)
    {
        $this->entityManager = $entityManager;
        //$this->formElementManager = $formElementManager;
        $this->formFactory = $formFactory;
        $this->name = $shortName;
    }
    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        /* // this gets them all, ordered
        $languages = $this->serviceManager->get('entity-manager')
                ->getRepository('Application\Entity\Language')
                ->findBy([],['name'=>'ASC']);
        */

        $page = $this->params()->fromQuery('page', 1);
        $repository = $this->entityManager->getRepository('Application\Entity\Language');
        $languages = $repository->findAll($page);

        return ['languages' => $languages];
    }

    /**
     * updates a language.
     *
     * @return ViewModel
     */
    public function editAction()
    {
        $id = $this->params()->fromRoute('id');
        if (!$id) {
            return $this->getFormViewModel(['errorMessage' => 'invalid or missing id parameter']);
        }
        $entity = $this->entityManager->find('Application\Entity\Language', $id);
        if (!$entity) {
            return $this->getFormViewModel(['errorMessage' => "language with id $id not found"]);
        }
        $form = $this->getForm(Language::class, ['object' => $entity, 'action' => 'update'])
               ->bind($entity);

        $viewModel = $this->getFormViewModel(
              ['form' => $form, 'title' => 'edit a language', 'id' => $id]
        );
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (!$form->isValid()) {
                return $viewModel;
            }
            $this->entityManager->flush();
            $this->flashMessenger()
                  ->addSuccessMessage("The language $entity has been updated.");
            $this->redirect()->toRoute('languages');
        }

        return $viewModel;
    }
    /**
     * deletes a language.
     *
     * @return bool
     */
    public function deleteAction()
    {
        echo 'YET TO BE IMPLEMENTED';

        return false;
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
                ['form' => $form, 'title' => 'add a language']);
        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (!$form->isValid()) {
                return $viewModel;
            }
            try {
                $this->entityManager->persist($language);
                $this->entityManager->flush();
                $this->flashMessenger()
                      ->addSuccessMessage("The language $language has been added.");
                $this->redirect()->toRoute('languages');
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
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
                ->setTemplate('application/languages/form.phtml');
    }
}
