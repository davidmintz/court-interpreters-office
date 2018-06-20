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
            $this->viewModel = new ViewModel();
            $this->viewModel
                ->setTemplate('interpreters-office/admin/events/form-2');
            if ($data) {
                $this->viewModel->setVariables($data);
            }
        }

        return $this->viewModel;
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
        $form->bind($event);
        $viewModel = $this->getViewModel()->setVariables(['form'  => $form,]);

        if ($request->isPost()) {
            $data = $request->getPost();
            $input = $data->get('event');
            $this->getEventManager()->trigger(
                'pre.validate',
                $this,
                ['input' => $data,]
            );
            $form->setData($data);
            if (! $form->isValid()) {
                if ($input) {
                    $defendantNames = isset($input['defendantNames']) ?
                        $input['defendantNames'] : [];
                    $interpreters = isset($input['interpreterEvents']) ?
                        $input['interpreterEvents'] : [];
                }//print_r($form->getMessages());
                return $viewModel
                    ->setVariables(compact('defendantNames', 'interpreters'));
            } else {
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
                return $this->redirect()->toRoute('events/view', ['id' => $event->getId()]);
            }
        }

        return $viewModel;
    }


    /**
     * edits a court interpreting event
     *
     */
    public function editAction()
    {
        $id = $this->params()->fromRoute('id');
        /** @var \InterpretersOffice\Entity\Event $entity  */
        $entity = $this->entityManager->find(Entity\Event::class, $id);
        if (! $entity) {
             return $this->getViewModel([
                'errorMessage'  =>
                 "event with id $id was not found in the database." ]);
        }
        $log = $this->getEvent()->getApplication()->getServiceManager()->get('log');


        $form = new Form\EventForm(
            $this->entityManager,
            ['action' => 'update','object' => $entity,]
        );
        $events = $this->getEventManager();
        $form->attach($events);
        $events->trigger('post.load', $this, ['entity' => $entity]);
        $request = $this->getRequest();
        $form->bind($entity);
        $log->debug(
            sprintf("at line %d, number of deftevents is: %d",
                __LINE__,
                $form->getObject()->getDefendantsEvents()->count())
        );
        $events->trigger('pre.populate');
        $modified = $entity->getModified();
        if ($request->isPost()) {
            $data = $request->getPost();
            $hydrator = new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($this->entityManager);
            $shit = $data['event'];
            if ($shit['submitter']) {
                unset($shit['anonymousSubmitter']);
            }
            $hydrator->hydrate($shit,$entity);
            printf("<pre>fucking data is:\n%s</pre>",print_r($shit,true));
            /*
            $deftEvents = $entity->getDefendantsEvents();
            foreach($deftEvents as $i => $shit) {
                $d = $shit->getDefendantName();
                echo " At $i ";
                echo $d->getId() . " is the bitch id ... ";
            }*/
            printf(
                "fucking number of defendantsEvents is now %d<br>",
                $entity->getDefendantsEvents()->count()
            );
            $this->entityManager->flush();
            return $this->getViewModel(['form' => $form, 'id' => $id]);
            $events->trigger('pre.validate', $this);
            $form->setData($data);
            $log->debug("fucking data? ".print_r($data->toArray(),true));
            $log->debug(
                sprintf("at line %d, number of deftevents is: %d",
                    __LINE__,
                    $form->getObject()->getDefendantsEvents()->count())
            );
            //printf("shit has %s defendantsEvents<br>",$entity->getDefendantsEvents()->count());
            //printf("shit has %s interpretersEvents<br>",$entity->getInterpreterEvents()->count());
            //$input = $data->get('event'); var_dump($input);
            if ($form->isValid()) {
                $events->trigger('post.validate', $this);
                try {
                    $log->debug(
                        sprintf("at line %d, number of deftevents is: %d",
                            __LINE__,
                            $form->getObject()->getDefendantsEvents()->count())
                    );
                    $this->entityManager->flush();
                    $url = $this->getEvent()->getApplication()
                        ->getServiceManager()
                        ->get('ViewHelperManager')->get('url')('events');
                    $date = $entity->getDate();
                    if ($modified != $entity->getModified() or
                        $this->params()->fromPost('deftnames_modified')) {
                        $verbiage = 'updated';
                    } else {
                        $verbiage = 'saved (unmodified)';
                    }
                    $this->flashMessenger()->addSuccessMessage(
                        sprintf(
                            "This event has been successfully $verbiage on the "
                            .'schedule for <a href="%s">%s</a>',
                            $url . $date->format('/Y/m/d'),
                            $date->format('l d-M-Y')
                        )
                    );
                    return $this->redirect()->toRoute(
                        'events/view',
                        ['id' => $entity->getId()]
                    );
                } catch (\Exception $e) {
                    /** @todo  need to do better than this */
                    echo $e->getMessage();
                    echo '<pre>';
                    print_r($_POST);
                    echo '</pre>';
                }
            }
            //printf('<pre>error:  %s</pre>',print_r($form->getMessages(),true));
            if ($form->hasTimestampMismatchError()) {
                $error = $form->getMessages()['modified']
                        [\Zend\Validator\Callback::INVALID_VALUE];
                $this->flashMessenger()->addErrorMessage($error);
                return $this->redirect()->toRoute('events/edit', ['id' => $id]);
            }
            /** @todo DRY this up somehow */
            $input = $data->get('event'); //var_dump($input['defendantNames']);
            if ($input) {
                $interpreters = isset($input['interpreterEvents']) ?
                        $input['interpreterEvents'] : [];
                $form->get('event')->get('anonymousSubmitter')
                    ->setValue($input['anonymousSubmitter']);
                $this->getViewModel()->setVariables(
                    compact('defendantNames', 'interpreters', 'form', 'id')
                );
            }
        } // not POST
        return $this->getViewModel(['form' => $form, 'id' => $id]);
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
}
