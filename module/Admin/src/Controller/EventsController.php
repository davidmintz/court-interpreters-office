<?php
/**
 * module/Admin/src/Controller/EventsController.php.
 */

namespace InterpretersOffice\Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Authentication\AuthenticationServiceInterface;
use InterpretersOffice\Admin\Form;
use InterpretersOffice\Entity;
use InterpretersOffice\Admin\Service\ScheduleUpdateManager;

/**
 *  EventsController
 *
 */

/* some handy SQL
 SELECT e.id, e.date, e.time, e.docket, t.name type, l.name language,
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
     * update listener
     * @var ScheduleUpdateManager
     */
    protected $updateManager;

    /**
     *  event form
     *
     *  @var Form\EventForm
     */
    protected $eventForm;

    /**
     * constructor
     *
     * @param EntityManagerInterface $em
     * @param AuthenticationServiceInterface $auth
     * @param ScheduleUpdateManager $updateManager
     */
    public function __construct(
        EntityManagerInterface $em,
        AuthenticationServiceInterface $auth,
        ScheduleUpdateManager $updateManager
    ) {
        $this->entityManager = $em;
        $this->auth = $auth;
        $this->updateManager = $updateManager;
    }

    /**
     * sets form
     *
     * @param FormEventForm $form
     * @return EventsController
     */
    public function setForm(Form\EventForm $form)
    {
        $this->form = $form;

        return $this;
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
        return $this->redirect()->toRoute('events');
    }

    /**
     * adds a new event
     */
    public function addAction()
    {
        $form = $this->form;
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
        /** handle multiple dates */
        if (isset($input['dates'])) {
            if (count($input['dates']) > 1) {
                return $this->batchCreate($input, $event);
            } else {
                $event->setDate(new \DateTime($input['dates'][0]));
            }
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

        return new JsonModel(['status' => 'success','id' => $event->getId()]);
    }

    /**
     * inserts multiple event entities
     *
     * @param  Array       $input
     * @param  EntityEvent $event
     * @return JsonModel
     */
    protected function batchCreate(Array $input, Entity\Event $event)
    {

        $event->setDate(new \DateTime(\array_shift($input['dates'])));
        $this->entityManager->persist($event);
        $dates = $input['dates'];
        sort($dates);
        $entities = [$event];
        foreach ($dates as $d) {
            $entity = new Entity\Event();
            $entity->setDate(new \DateTime($d));
            foreach (['time','docket','judge','language','eventType','location','anonymousJudge',
            'submissionDate','submissionTime', 'submitter','anonymousSubmitter','endTime','cancellationReason',
            'comments','adminComments',
            ] as $prop) {
                $getter = 'get'.ucfirst($prop);
                $datum = $event->$getter();
                $setter = 'set'.ucfirst($prop);
                $entity->$setter($datum);
            }
            foreach ($event->getDefendants() as $d) {
                $entity->addDefendant($d);
            }
            foreach ($event->getInterpreterEvents() as $ie) {
                $entity->assignInterpreter($ie->getInterpreter());
            }
            $this->entityManager->persist($entity);
            $entities[] = $entity;
        }
        $this->entityManager->flush();
        $url = $this->getEvent()->getApplication()->getServiceManager()
        ->get('ViewHelperManager')->get('url')('events');
        $date = $event->getDate();
        $this->flashMessenger()->addSuccessMessage(sprintf(
            'This event has been added to the schedule for <a href="%s">%s</a> and %s other %s',
            $url . $date->format('/Y/m/d'),
            $date->format('l d-M-Y'),
            count($dates),
            (count($dates) > 1 ? 'dates' : 'date')
        ));

        return new JsonModel(['status' => 'success','ids' => array_map(function ($e) {
            return $e->getId();
        }, $entities)]);
    }

    /**
     * edits an event
     *
     */
    public function editAction()
    {
        $id = $this->params()->fromRoute('id');
        /** @var \InterpretersOffice\Entity\Event $entity  */
        $entity = $this->form->getObject();
        if (! $entity->getId()) {
            // i.e. if it's an empty object the factory injected when it couldn't find it by id...
            return ['errorMessage' => "No event with id $id was found in the database.",
            'header' => 'event not found'];
        }
        if ($entity->isDeleted()) {
            return ['errorMessage' =>
            'This event has been deleted from the schedule, and therefore is no longer mutable.
             If you need to have it restored, please contact your database administrator.',
            'header' => 'event deleted'];
        }
        $form = $this->form;
        $view = $this->getViewModel(['id' => $id,'form' => $form]);
        $events = $this->getEventManager();
        $form->attach($events);
        $modified_before = $entity->getModified()->format('Y-m-d h:i:s');
        $form->bind($entity);

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
            $verbiage = 'saved (unchanged)';
        }
        $date = $entity->getDate();
        $this->flashMessenger()->addSuccessMessage(sprintf(
            "This event has been $verbiage on the "
            .'schedule for <a href="%s">%s</a>.',
            $url . $date->format('/Y/m/d'),
            $date->format('l d-M-Y')
        ));

        return new JsonModel(['status' => 'success', 'id' => $id]);
    }

    /**
     * deletes an entity
     *
     * @return JsonModel|ViewModel
     */
    public function deleteAction()
    {
        $id = $this->params()->fromRoute('id');
        if (! $this->getRequest()->isPost()) {
            return $this->redirect()->toRoute('events');
        }
        $validator = new \Laminas\Validator\Csrf('csrf', ['timeout' => 600]);
        $token = $this->params()->fromPost('csrf');
        if (! $validator->isValid($token)) {
            return new JsonModel(['status' => 'error','message' =>
                'Invalid or missing security token. '
                .'You may need to refresh this page and try again.']);
        }
        $entity = $this->entityManager->find(Entity\Event::class, $id);
        if (! $entity) {
            return new JsonModel([
                'status' => 'ENTITY NOT FOUND',
                'deleted' => false,
                'message' => "Event id $id was not found in the database"
            ]);
        }
        if ($entity->getDeleted()) {
            return new JsonModel([
                'status' => 'success',
                'deleted' => true,
                'message' => "This event has already been deleted",
            ]);
        }

        $entity->setDeleted(true);
        $this->getEventManager()->trigger(
            'deleteEvent',
            $this,
            ['entity' => $entity,
            'email_notification' => $this->params()->fromPost('email_notification')]
        );
        $this->entityManager->flush();
        $x = $this->getEventManager()->trigger('postFlush', $this);

        return new JsonModel(['deleted' => true,'status' => 'success',
            'debug' => 'FYI trigger returns: '.get_class($x),
            'message' => "this event has been deleted"]);
    }

    /**
     * generates markup for an interpreter
     *
     * @return Laminas\Http\PhpEnvironment\Response
     * @throws \RuntimeException
     */
    public function interpreterTemplateAction()
    {
        $helper = new Form\View\Helper\InterpreterElementCollection();
        $factory = new \Laminas\InputFilter\Factory();
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
            $result = $repository->getInterpreterOptionsForLanguage($language_id, ['with_banned_data' => true]);
        }
        if ($this->params()->fromQuery('csrf')) {
            $csrf = (new \Laminas\Validator\Csrf('csrf'))->getHash();
            $result = [
                'csrf' => $csrf,
                'options' => $result,
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
        if (! $entity or $entity->isDeleted()) {
            return new JsonModel([
                'status' => 'error',
                'message' => "This event (id $id) was not found in the database.",
            ]);
        }
        if ($this->getRequest()->isPost()) {

            /** @var \InterpretersOffice\Entity\Event $entity  */
            $form = new Form\EventForm(
                $this->entityManager,
                ['action' => 'update','object' => $entity,]
            );
            $events = $this->getEventManager();
            $form->attach($events);
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
            $events->trigger('pre.validate', $this);
            if (! $form->isValid()) {
                return new JsonModel(
                    ['validation_errors' => $form->getMessages()]
                );
            }
            $events->trigger('post.validate', $this);
            $this->entityManager->flush();
            $collection = $entity->getInterpreterEvents();
            $html = '';
            $helper = new \InterpretersOffice\View\Helper\InterpreterNames();
            $template = $helper->template;
            foreach ($collection as $ie) {
                $i = $ie->getInterpreter();
                $html .= sprintf(
                    $template,
                    $i->getId(),
                    $i->getLastname(),
                    $i->getFirstname(),
                    $ie->getSentConfirmationEmail() ? $helper->check : ''
                    
                );
            }
            return new JsonModel([
                'status' => 'success',
                'html' => $html,
            ]);
        }
    }
}
