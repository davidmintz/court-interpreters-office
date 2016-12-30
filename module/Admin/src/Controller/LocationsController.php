<?php
/**
 * module/InterpretersOffice/src/Controller/IndexController.php.
 */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
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
            FormFactoryInterface $formFactory, $shortName = null)
    {
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
           // echo "$id is our id!";
           // return a list of that type
        }
        $view = new ViewModel(compact('locationTypes')); 
        return $view->setVariables(['title'=>'locations']);
    }
    /**
     * adds a new Location.
     * @todo refactor, start passing some sort of context (query?) parameter to
     * our form so it can know what option data to fetch for the select 
     * elements
     * @return ViewModel
     */
    public function addAction()
    {
        
        $entity = new Location();
        
        $form = $this->getForm(
                Location::class, [
                    'object' => $entity,
                    'action' => 'create',
                    'form_context' => $this->params()->fromQuery('form_context','locations')
                    ])
               ->bind($entity);

        $viewModel = (new ViewModel([
            'form' => $form, 
            'title' => 'add a location',          
            ]))
            ->setTemplate('interpreters-office/admin/locations/form.phtml');

        $request = $this->getRequest();
       
        
        if ($request->isPost()) {
            $json = $request->isXmlHttpRequest();
            $form->setData($request->getPost());
            if (!$form->isValid()) {
                return $json ? new JsonModel(
                     [
                         'valid' => false,
                          'id'   => null,
                         'validationErrors' => $form->getMessages(),
                     ]
                ) : $viewModel;
            }
            
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
            if ($json) {
                return new JsonModel(
                     [
                        'valid' => true,
                        'validationErrors' => null,
                        'entity'   => [
                            'name' => $entity->getName(),
                            'id'   => $entity->getId(),
                            'type' => (string) $entity->getType(),
                        ],                    
                     ]
                );
            }
            $this->flashMessenger()
                  ->addSuccessMessage("The location  <strong>{$entity->getName()}</strong> has been added.");
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
        $viewModel = (new ViewModel())
            ->setTemplate('interpreters-office/admin/locations/form.phtml')
            ->setVariables(['title' => 'edit a location']);

        $id = $this->params()->fromRoute('id');
        
        if (!$id) { // get rid of this, since it will otherwise be 404?
            return $viewModel->setVariables(['errorMessage' => 'invalid or missing id parameter']);
        }

        $entity = $this->entityManager->find('InterpretersOffice\Entity\Location', $id);
        if (!$entity) {
            return $viewModel->setVariables(['errorMessage' => "location with id $id not found"]);
        } else {
            $viewModel->id = $id;
        }

        $form = $this->getForm(Location::class,
                ['object' => $entity, 'action' => 'update'])
               ->bind($entity);
        $viewModel->form = $form;

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (!$form->isValid()) {
                return $viewModel;
            }
            $this->entityManager->flush();
            $this->flashMessenger()
                  ->addSuccessMessage("The location <strong>{$entity->getName()}</strong> has been updated.");
            $this->redirect()->toRoute('locations');
        }

        return $viewModel;
    }
    
    public function courtroomsAction()
    {
        $parent_id = $this->params()->fromRoute('parent_id');
        $repository = $this->entityManager->getRepository('InterpretersOffice\Entity\Location');
        $data = $repository->getCourtrooms($parent_id);
        return new JsonModel($data);
    }
}
