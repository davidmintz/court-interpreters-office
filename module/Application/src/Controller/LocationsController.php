<?php
/**
 * module/Application/src/Controller/IndexController.php.
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Application\Form\Factory\FormFactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Application\Entity\Location;
use Application\Form\AnnotatedFormCreationTrait;

/**
 * LocationsController.
 *
 * For managing the locations to which interpreters are deployed
 */
class LocationsController extends AbstractActionController
{
    use AnnotatedFormCreationTrait;

    /**
     * implementation of FormFactoryInterface.
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
     * short name of this controller.
     *
     * @var string
     */
    protected $name;

     /**
     * constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param AbstractPluginManager  $formElementManager
     * @param string $shortName this controller's short name/type of entity
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
        echo 'hurray for indexAction.';

        return [];
    }
    /**
     * adds a new Location.
     *
     * @return ViewModel
     */
    public function addAction()
    {
        $entity = new Location();
        $form = $this->getForm(Location::class, ['object' => $entity, 'action' => 'create'])
               ->bind($entity);

        return (new ViewModel(['form' => $form, 'title' => 'add a location']))
            ->setTemplate('application/locations/form.phtml');
    }
    /**
     * edits a Location.
     *
     * @return ViewModel
     */
    public function editAction()
    {
        $viewModel = (new ViewModel())
        ->setTemplate('application/locations/form.phtml')
        ->setVariables(['title' => 'edit a location']);

        $id = $this->params()->fromRoute('id');
        if (!$id) {
            return $viewModel->setVariables(['errorMessage' => 'invalid or missing id parameter']);
        }
        $entity = $this->entityManager->find('Application\Entity\Location', $id);
        if (!$entity) {
            return $viewModel->setVariables(['errorMessage' => "location with id $id not found"]);
        }

        $form = $this->getForm(Location::class, ['object' => $entity, 'action' => 'create'])
               ->bind($entity);

        return (new ViewModel(['form' => $form, 'title' => 'edit a location']))
            ->setTemplate('application/locations/form.phtml');
    }
}
