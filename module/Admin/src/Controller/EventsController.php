<?php
/**
 * module/Admin/src/Controller/EventsController.php.
 */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Doctrine\ORM\EntityManagerInterface;

use Zend\EventManager\Event;
use Zend\Authentication\AuthenticationServiceInterface;

use InterpretersOffice\Admin\Form;

use InterpretersOffice\Entity;
use InterpretersOffice\Controller\ExceptionHandlerTrait;

/**
 *  EventsController
 *
 */

/* some handy SQL
 SELECT e.id, e.date, e.time, t.name type, l.name language,
 COALESCE(j.lastname, a.name) AS judge, p.name AS place,
 COALESCE(s.lastname,as.name) submitter, submission_date, submission_time FROM events e
 JOIN event_types t ON e.event_type_id = t.id
 JOIN languages l ON e.language_id = l.id
 LEFT JOIN people j ON j.id = e.judge_id
 LEFT JOIN anonymous_judges a ON a.id = e.anonymous_judge_id
 LEFT JOIN people s ON e.submitter_id = s.id
 LEFT JOIN hats AS `as` ON e.anonymous_submitter_id = as.id
 LEFT JOIN locations p ON e.location_id = p.id;
 */

/**
 * events controller
 *
 * controller for inserting and updating court interpreting events
 *
 */
class EventsController extends AbstractActionController
{


    use ExceptionHandlerTrait;

    /**
     * entity manager
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * authentication service
     *
     * @var AuthenticationServiceInterface
     */
    protected $auth;

    /**
     * view
     *
     * @var ViewModel
     */
    protected $viewModel;

    /**
     * constructor
     *
     * @param EntityManagerInterface $em
     * @param AuthenticationServiceInterface $auth
     */
    public function __construct(
        EntityManagerInterface $em,
        AuthenticationServiceInterface $auth
    ) {
        $this->entityManager = $em;
        $this->auth = $auth;
    }

    /**
     * lazy-instantiates and returns ViewModel
     *
     * @param array $data view variables to set
     * @return ViewModel
     */
    public function getViewModel(array $data = [])
    {
        if (! $this->viewModel) {
            $this->viewModel = new ViewModel($data);
        }

        return $this->viewModel->setVariables($data);
    }

    /**
     * index action
     *
     */
    public function indexAction()
    {
        return ['title' => 'schedule'];
    }

    /**
     * adds a new event
     */
    public function addAction()
    {
        $form = new Form\EventForm(
            $this->entityManager,
            ['action' => 'create',
             'auth_user_role' => $this->auth->getIdentity()->role,
             'object' => null,]
        );
        $form->attach($this->getEventManager());
        $request = $this->getRequest();
        $form->setAttribute('action', $request->getRequestUri());
        $event = new Entity\Event();
        $viewModel = $this->getViewModel(['form'  => $form,]);

        if (! $request->isPost()) {
            $id = $this->params()->fromRoute('id');
            if ($id) {
                $other = $this->entityManager->find(Entity\Event::class, $id);
                if ($other) {
                    $event
                    ->setDocket($other->getDocket())
                    ->setLanguage($other->getLanguage())
                    ->addDefendants($other->getDefendants())
                    ->setJudge($other->getJudge())
                    ->setAnonymousJudge($other->getAnonymousJudge());
                } else {
                    $viewModel->warning_message =
                    "The event with id $id was not found, so we can't use it
                    to create a repeat event";
                }
            }
            $form->bind($event);
            return $viewModel;
        }
        try {
            $data = $request->getPost();
            $input = $data->get('event');
            $form->bind($event);
            $this->getEventManager()->trigger(
                'pre.validate',
                $this,
                ['input' => $data,]
            );
            $form->setData($data);
            if (! $form->isValid()) {
                return new JsonModel(
                    ['validation_errors' => $form->getMessages()]
                );
            }
            $this->entityManager->persist($event);
            $this->entityManager->flush();
            $url = $this->getEvent()->getApplication()->getServiceManager()
            ->get('ViewHelperManager')->get('url')('events');
            $date = $event->getDate();
            $this->flashMessenger()->addSuccessMessage(sprintf(
                'This event has been added to the schedule for <a href="%s">%s</a>',
                $url . $date->format('/Y/m/d'),
                $date->format('l d-M-Y')
            ));

            return new JsonModel(
                ['status' => 'success','id' => $event->getId()]
            );
        } catch (\Exception $e) {
            return $this->catch($e);
        }
    }

    /**
     * edits an event
     *
     */
    public function editAction()
    {
        $id = $this->params()->fromRoute('id');
        /** @var \InterpretersOffice\Entity\Event $entity  */
        $entity = $this->entityManager->getRepository(Entity\Event::class)
            ->load($id);

        if (! $entity) {
            return ['errorMessage' => "No event with id $id was found in the database.",'header'=>'error: event not found'];
        }
        $form = new Form\EventForm(
            $this->entityManager,
            ['action' => 'update','object' => $entity,]
        );
        $view = $this->getViewModel(['id' => $id,'form' => $form]);
        $events = $this->getEventManager();
        $form->attach($events);
        $modified_before = $entity->getModified()->format('Y-m-d h:i:s');
        $form->bind($entity);
        $date = $entity->getDate();
        $events->trigger(
            'pre.populate',
            $this,
            ['entity' => $entity, 'form' => $form]
        );
        if ($this->getRequest()->isGet()) {
            return $view;
        }
        $events->trigger('pre.validate', $this);
        $form->setData($this->getRequest()->getPost());
        try {
            if (! $form->isValid()) {
                return new JsonModel(
                    ['validation_errors' => $form->getMessages()]
                );
            }
            $events->trigger('post.validate', $this);
            $this->entityManager->flush();
            $url = $this->getEvent()->getApplication()
                ->getServiceManager()->get('ViewHelperManager')
                ->get('url')('events');
            if ($modified_before != $entity->getModified()->format('Y-m-d h:i:s')
                or $this->params()->fromPost('deftnames_modified')) {
                $verbiage = 'updated';
            } else {
                $verbiage = 'saved (unmodified)';
            }
            $this->flashMessenger()->addSuccessMessage(sprintf(
                "This event has been $verbiage on the "
                .'schedule for <a href="%s">%s</a>.',
                $url . $date->format('/Y/m/d'),
                $date->format('l d-M-Y')
            ));

            return new JsonModel(['status' => 'success', 'id' => $id]);
        } catch (\Exception $e) {
            return $this->catch($e);
        }
    }

    /**
     * deletes an entity
     *
     * @return JsonModel
     */
    public function deleteAction()
    {
        if (! $this->getRequest()->isPost()) {
            return $this->redirect()->toRoute('events');
        }
        $id = $this->params()->fromRoute('id');
        $validator = new \Zend\Validator\Csrf('csrf');
        $token = $this->params()->fromPost('csrf');
        if (! $validator->isValid($token)) {
            return new JsonModel(['status' => 'error','message' =>
                'Invalid or missing security token. '
                .'You may need to refresh this page and try again.']);
        }
        $entity = $this->entityManager->find(Entity\Event::class, $id);
        if (! $entity) {
            $result = [
                'status' => 'ENTITY NOT FOUND',
                'deleted' => false,
                'message' => "Event id $id was not found in the database"
            ];
            return new JsonModel($result);
        }
        try {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
            $this->flashMessenger()->addSuccessMessage(
                sprintf(
                    'this event (%s) has been deleted from the schedule',
                    $entity->describe()
                )
            );
            return new JsonModel(['deleted' => true,'status' => 'success',
                'message' => "this event has been deleted"]);
        } catch (\Exception $e) {
            return new JsonModel(['deleted' => false,'status' => 'error',
                'message' => $e->getMessage()]);
        }
    }
    /**
     * generates markup for an interpreter
     *
     * @return Zend\Http\PhpEnvironment\Response
     * @throws \RuntimeException
     */
    public function interpreterTemplateAction()
    {
        $helper = new Form\View\Helper\InterpreterElementCollection();
        $factory = new \Zend\InputFilter\Factory();
        $inputFilter = $factory->createInputFilter(
            $helper->getInputFilterSpecification()
        );
        $data = $this->params()->fromQuery();
        $inputFilter->setData($data);
        if (! $inputFilter->isValid()) {
            throw new \RuntimeException(
                "bad input parameters: "
                    .json_encode($inputFilter->getMessages(), \JSON_PRETTY_PRINT)
            );
        }
        // $data['created_by'] = "0";//$this->auth->getStorage()->read()->id;
        $html = $helper->fromArray($data);
        return $this->getResponse()->setContent($html);
    }


    /**
     * gets interpreter options for populating select
     *
     * @return JsonModel
     */
    public function interpreterOptionsAction()
    {
        /** @var  \InterpretersOffice\Entity\Repository\InterpreterRepository $repository */
        $repository = $this->entityManager->getRepository(Entity\Interpreter::class);
        $language_id = $this->params()->fromQuery('language_id');
        if (! $language_id) {
            $result = ['error' => 'missing language id parameter'];
        } else {
            $result = $repository->getInterpreterOptionsForLanguage($language_id);
        }
        if ($this->params()->fromQuery('csrf')) {
            $csrf = (new \Zend\Validator\Csrf('csrf'))->getHash();
            $result = [
                'options' => $result,
                'csrf' => $csrf,
            ];
        }
        return new JsonModel($result);
    }

    /**
     * gets last modification timestamp for entity
     *
     * @return JsonModel
     */
    public function getModificationTimeAction()
    {
        $id = $this->params()->fromRoute('id');
        $modified = $this->entityManager->getRepository(Entity\Event::class)
            ->getModificationTime($id);
        if (is_string($modified)) {
            return new JsonModel(['modified' => $modified]);
        } else { // array with error message
            return new JsonModel($modified);
        }
    }

    /**
     * handles updating interpreters within schedule page
     * @return JsonModel
     */
    public function updateInterpretersAction()
    {

        $id = $this->params()->fromRoute('id');
        $entity = $this->entityManager->find(Entity\Event::class, $id);
        if (! $entity) {
            return new JsonModel([
                'status' => 'error',
                'message' => "The event with id $id was not found in the database.",
            ]);
        }
        if ($this->getRequest()->isPost()) {

            /** @var \InterpretersOffice\Entity\Event $entity  */
            $form = new Form\EventForm(
                $this->entityManager,
                ['action' => 'update','object' => $entity,]
            );
            $form->bind($entity)
                ->setValidationGroup([
                'csrf', 'event' => [
                    'interpreterEvents' => [
                        'interpreter',
                        'event',
                    ]
                ],
                ]);
            $form->setData($this->getRequest()->getPost());
            if (! $form->isValid()) {
                return new JsonModel(
                    ['validation_errors' => $form->getMessages()]
                );
            }
            $this->entityManager->flush();
            $collection = $entity->getInterpreterEvents();
            $html = '';
            $template = (new \InterpretersOffice\View\Helper\InterpreterNames())
                ->template;
            foreach ($collection as $ie) {
                $i = $ie->getInterpreter();
                $html .= sprintf(
                    $template,
                    $i->getId(),
                    $i->getLastname(),
                    $i->getFirstname()
                );
            }
            return new JsonModel([
                'status' => 'success',
                'html' => $html,
            ]);
        }
    }
}
