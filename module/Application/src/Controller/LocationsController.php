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
     * @todo do we really use this for anything?
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
            FormFactoryInterface $formFactory, $shortName = null)
    {
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
       // $this->name = $shortName;
    }
    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $viewModel = new ViewModel;
        return $viewModel;
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

        $viewModel = (new ViewModel(['form' => $form, 'title' => 'add a location']))
            ->setTemplate('application/locations/form.phtml');

        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (!$form->isValid()) {
                echo "SHIT NOT VALID?";  print_r($form->getMessages());
                return $viewModel;
            }
            try {
                $this->entityManager->persist($entity);
                $this->entityManager->flush();
                $this->flashMessenger()
                      ->addSuccessMessage("The location has been added.");
                $this->redirect()->toRoute('locations');
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
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
            ->setTemplate('application/locations/form.phtml')
            ->setVariables(['title' => 'edit a location']);

        $id = $this->params()->fromRoute('id');
        
        if (!$id) { // get rid of this, since it will be 404?
            return $viewModel->setVariables(['errorMessage' => 'invalid or missing id parameter']);
        }
        
        $entity = $this->entityManager->find('Application\Entity\Location', $id);
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
                echo "SHIT NOT VALID?";  print_r($form->getMessages());
                return $viewModel;
            }
            try {
                
                $this->entityManager->flush();
                $this->flashMessenger()
                      ->addSuccessMessage("The location has been updated.");
                //$this->redirect()->toRoute('locations');
                echo "YAY. don't forget to redirect() ";
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }


        
        return (new ViewModel(['form' => $form,'id'=>$id, 'title' => 'edit a location']))
            ->setTemplate('application/locations/form.phtml');
        // to be continued
    }
}
