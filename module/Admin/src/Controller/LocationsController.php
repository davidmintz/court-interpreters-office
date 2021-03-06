<?php
/**
 * module/InterpretersOffice/src/Controller/IndexController.php.
 */

namespace InterpretersOffice\Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use InterpretersOffice\Form\Factory\FormFactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Entity\Location;
use InterpretersOffice\Form\AnnotatedFormCreationTrait;

/**
 * LocationsController.
 *
 * For managing the locations to which interpreters are deployed
 */
class LocationsController extends AbstractActionController
{
    use AnnotatedFormCreationTrait;
    use DeletionTrait;

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
     * @todo do we really use this for anything?
     *
     * @var string
     */
    protected $name;

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
    }
    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $repo = $this->entityManager->getRepository('InterpretersOffice\Entity\LocationType');
        $locationTypes = $repo->findAllWithTotals();
        if ($id = $this->params()->fromRoute('id')) {
            $repo = $this->entityManager->getRepository('InterpretersOffice\Entity\Location');
            $data = $repo->findByTypeId($id);

            return new JsonModel($data);
        }
        $view = new ViewModel(compact('locationTypes'));

        return $view->setVariables(['title' => 'locations']);
    }
    /**
     * adds a new Location.
     *
     * @return ViewModel
     */
    public function addAction()
    {
        $entity = new Location();

        $form = $this->getForm(
            Location::class,
            [
                    'object' => $entity,
                    'action' => 'create',
                    'form_context' => $this->params()->fromQuery('form_context', 'locations'),
            ]
        )->bind($entity);

        $viewModel = new ViewModel([
            'form' => $form,
            'title' => 'add a location',
        ]);

        $viewModel->type_id = $this->params()->fromRoute('type_id');

        $request = $this->getRequest();

        if ($request->isPost()) {
            $json = $request->isXmlHttpRequest();
            $form->setData($request->getPost());
            if (! $form->isValid()) {
                return $json ? new JsonModel(
                    [
                         'valid' => false,
                          'id' => null,
                         'validationErrors' => $form->getMessages(),
                    ]
                ) : $viewModel;
            }

            $this->entityManager->persist($entity);
            $this->entityManager->flush();
            if ($json) {
                $parentLocation = $entity->getParentLocation();

                return new JsonModel(
                    [
                        'valid' => true,
                        'validationErrors' => null,
                        'entity' => [
                            'name' => $entity->getName(),
                            'id' => $entity->getId(),
                            'type' => (string) $entity->getType(),
                            'parent_location_id' => $parentLocation ?
                                    $parentLocation->getId() : null,
                        ],
                     ]
                );
            }
            $this->flashMessenger()->addSuccessMessage(
                "The location  <strong>{$entity->getName()}</strong> has been added."
            );
            $this->redirect()->toRoute('locations');
        }

        return $viewModel;
    }
    /**
     * edits a Location.
     *
     * @return ViewModel
     */
    public function editAction()
    {
        $viewModel = new ViewModel(['title' => 'edit a location']);
        $id = $this->params()->fromRoute('id');
        $repo = $this->entityManager->getRepository(Location::class);
        $entity = $repo->find($id);
        if (! $entity) {
            return $viewModel->setVariables(['errorMessage' => "location with id $id not found"]);
        }
        $form = $this->getForm(
            Location::class,
            [   'object' => $entity, 'action' => 'update',
            ]
        )
            ->bind($entity);
        $viewModel->setVariables(['form' => $form,
            'has_related_entities' => $repo->hasRelatedEntities($id)]);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (! $form->isValid()) {
                return $viewModel;
            }
            $this->entityManager->flush();
            $this->flashMessenger()
                  ->addSuccessMessage("The location <strong>{$entity->getName()}</strong> has been updated.");
            $this->redirect()->toRoute('locations');
        }

        return $viewModel;
    }
    /**
     * gets courtrooms located at a courthouse.
     *
     * returns as JSON data the courtrooms whose parent courthouse
     * id is submitted as a route parameter. for populating select elements
     * via xhr.
     *
     * @return JsonModel
     */
    public function courtroomsAction()
    {
        $parent_id = $this->params()->fromRoute('parent_id');
        $repository = $this->entityManager->getRepository('InterpretersOffice\Entity\Location');
        $data = $repository->getCourtroomValueOptions($parent_id);

        return new JsonModel($data);
    }

    /**
     * deletes a location.
     * @todo logging?
     * @return JsonModel
     */
    public function deleteAction()
    {

        $request = $this->getRequest();
        if ($request->isPost()) {
            $id = $this->params()->fromRoute('id');
            $name = $this->params()->fromPost('name');
            $what = 'location';
            $entity = $this->entityManager->find(Location::class, $id);

            return $this->delete(compact('entity', 'id', 'name', 'what'));
        }
    }
}
