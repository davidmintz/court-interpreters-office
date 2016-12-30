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
class EventTypesController extends AbstractActionController
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
        FormFactoryInterface $formFactory,
        $shortName = null
    ) {

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
        $repository = $this->entityManager->getRepository('InterpretersOffice\Entity\EventType');
        $eventTypes = $repository->findAll();
        return ['title' => 'event-types','eventTypes' => $eventTypes ];
    }

    /**
     * add action
     *      *
     * @return ViewModel
     */
    public function addAction()
    {

        $view = (new ViewModel(['title' => "add an event-type"]))
                ->setTemplate("interpreters-office/admin/{$this->name}/form.phtml");
        $entity = new EventType;
        $form = $this->getForm(EventType::class, ['object' => $entity, 'action' => 'create'])
                ->bind($entity);
        $view->setVariables(['form' => $form, 'id' => $entity->getId()]);

        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (! $form->isValid()) {
                return $view;
            }
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
            $this->flashMessenger()
                  ->addSuccessMessage("The event-type <strong>$entity</strong> has been added.");
            $this->redirect()->toRoute('event-types');
        }

        return $view;
    }

    /**
     * edits an EventType entity
     *
     */
    public function editAction()
    {
        $view = (new ViewModel(['title' => "edit an event-type"]))
                ->setTemplate("interpreters-office/admin/{$this->name}/form.phtml")
                ->setVariables(['title' => 'edit an event-type']);
        $id = $this->params()->fromRoute('id');
        if (! $id) {
            return $view->setVariables(['errorMessage' => 'invalid or missing id parameter']);
        }
        $entity = $this->entityManager->find('InterpretersOffice\Entity\EventType', $id);
        if (! $entity) {
            return $view->setVariables(['errorMessage' => "event-type with id $id not found"]);
        }
        $form = $this->getForm(EventType::class, ['object' => $entity, 'action' => 'update'])
               ->bind($entity);

        $view->setVariables(['form' => $form,'id' => $id]);

        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (! $form->isValid()) {
                return $view;
            }
            $this->entityManager->flush();
            $this->flashMessenger()
                  ->addSuccessMessage("The event-type <strong>$entity</strong> has been updated.");
            $this->redirect()->toRoute('event-types');
        }

        return $view;
    }
}
