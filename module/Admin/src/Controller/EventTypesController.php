<?php
/**
 * module/Admin/src/Controller/EventTypesController.php
 */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
//use Zend\ServiceManager\AbstractPluginManager;
use InterpretersOffice\Form\Factory\FormFactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Entity\EventType;

use InterpretersOffice\Form\AnnotatedFormCreationTrait;

/**
 * controller for managing event-types
 *
 *
 */
class EventTypesController extends AbstractActionController {
    
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
    protected $name = 'event-types';
    
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
        if (! $this->name) {
            $this->name = $shortName;
        }
    }
    
    
    /**
     * index action
     */
    public function indexAction()
    {
        
    }
    
    /**
     * add action. work in progress
     * 
     * @return ViewModel
     */
    public function addAction()
    {
        
        $view = new ViewModel();
        $entity = new EventType;

        $form = $this->getForm(EventType::class, ['object' => $entity, 'action' => 'create'])
                ->bind($entity);
        $view->setTemplate("interpreters-office/admin/{$this->name}/form.phtml")
            ->setVariables(['form'=>$form,'title'=>'add new event-type']);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (!$form->isValid()) {
                echo "Not valid?";
                print_r($form->getMessages());
                return $view;
            }
            echo "valid...";
            try {
                $this->entityManager->persist($entity);
                $this->entityManager->flush();
                $this->flashMessenger()
                      ->addSuccessMessage("The event-type $entity has been added.");
                //$this->redirect()->toRoute('event-types');
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        return $view;
    }
    
    
}
