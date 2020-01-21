<?php /** module/Notes/src/Controller/NotesController.php */
namespace InterpretersOffice\Admin\Notes\Controller;

use Laminas\Mvc\Controller\AbstractRestfulController;
//use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Admin\Notes\Service\NotesService;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use InterpretersOffice\Entity\User;
use InterpretersOffice\Admin\Notes\Entity\MOTD;
use InterpretersOffice\Admin\Notes\Entity\MOTW;
use Parsedown;
use DateTime;

class NotesController extends AbstractRestfulController
{

    /*
     * entity manager
     *
     * @var EntityManagerInterface
     private $em;
     */

    /**
     *
     * @var stdClass $user
     * private $user;
     */

    /**
     * notes service
     *
     * @var NotesService
     */
    private $notesService;

    /**
     * constructor
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(NotesService $notesService)
    {
        $this->notesService = $notesService;
    }



    /**
     * updates entity
     *
     * @param  $id  entity id
     * @param  array $data
     *
     * @return JsonModel
     */
    public function update($id, $data)
    {
        $type =  $this->params()->fromRoute('type');
        if ($type == 'motd' && isset($data['dates'])) {
            return $this->batchEdit($data);
        }
        $service = $this->notesService;
        $inputFilter = $service->getInputFilter();
        $inputFilter->setData($data);
        if (! $inputFilter->isValid()) {
            $errors = $inputFilter->getMessages();
            return new JsonModel([
                'validation_errors' => $errors,
            ]);
        }

        return new JsonModel(
            $service->update($type, (int)$id, $inputFilter->getValues())
        );

    }

    /**
     *
     * @param  Array  $data
     * @param  int $id
     * @return JsonModel
     */
    protected function batchEdit(Array $data): JsonModel
    {
        return new JsonModel($this->notesService->batchEdit($data));
    }

    /**
     * creates MOT[DW]
     *
     * @param  array $data
     * @return JsonModel
     */
    public function create($data)
    {
        $type =  $this->params()->fromRoute('type');
        $data['type'] = $type;
        if ($type == 'motd' && isset($data['dates'])) {
            return $this->batchEdit($data);
        }
        $service = $this->notesService;
        $inputFilter = $service->getInputFilter();
        $inputFilter->remove('modified');
        $service->addDateValidation($inputFilter,$type);

        $inputFilter->setData($data);
        if (! $inputFilter->isValid()) {
            $errors = $inputFilter->getMessages();
            return new JsonModel([
                'validation_errors' => $errors,
            ]);
        }

        return new JsonModel($service->create($inputFilter->getValues()));
    }

    /**
     * gets the MOTD and/or MOTD for a date
     *
     * @return JsonModel
     */
    public function get($date)
    {
        $view_class = $this->getRequest()->isXMLHttpRequest() ? JsonModel::class : ViewModel::class;
        $type =  strtoupper($this->params()->fromRoute('type','all'));
        /** @var $service InterpretersOffice\Admin\Notes\Service\NotesService */
        $service = $this->notesService;
        try {
            $date_obj = new DateTime($date);
        } catch (\Exception $e) {
            throw new \RuntimeException("invalid date parameter: '$date'");
        }
        if ('ALL' != $type) {
            $message = $service->getNoteByDate($date_obj, $type);
            $view = new $view_class([$type => $message]);
        } else {
            $messages = $service->getAllForDate($date_obj);
            $view = new $view_class($messages);
        }
        if ($view_class == JsonModel::class) {
            // trigger event so that, e.g., TaskRotationService can
            // inject relevant data into view
            //---------------------------------
            $events = $this->getEventManager();
            /** @todo addIdentifiers() just once e.g., in an onBootstrap */
            $events->addIdentifiers(['Notes']);
            $this->getEvent()->getApplication()->getServiceManager()
                ->get('log')->debug('triggering NOTES_RENDER in NotesController');
            $note_types = $type == 'ALL' ? ['motd','motw'] : [strtolower($type)];
            $events->trigger('NOTES_RENDER','Notes',[
                'date' => new \DateTime($date),
                'event' => $this->getEvent(),
                'settings' => $service->getSession()->settings,
                'note_types' => $note_types,
                'view' => $view,
            ]);
        }

        return $view;
    }

    /**
     * Updates the users MOT[DW] settings.
     *
     * When the user modifies the visibility, size or position of the MOT[DW]
     * element, we get an xhr request so we can persist the new settings.
     *
     * @return JsonModel
     */
    public function updateSettingsAction()
    {
        $params = $this->params()->fromPost();
        $this->notesService->updateSettings($params);

        return new JsonModel($this->notesService->getSession()->settings);
    }

    /**
     * equivalent of indexAction
     *
     * @return ViewModel
     */
    public function getList()
    {
        $log = $this->getEvent()->getApplication()->getServiceManager()->get('log');
        $route = $this->getEvent()->getRouteMatch()->getMatchedRouteName();
        $date = $this->notesService->getSession()->settings['date'] ?? date('Y-m-d');
        $notes = $this->notesService->getAllForDate(new \DateTime($date));

        return ['notes' => $notes,];
    }

    /**
     * renders form for MOTD
     *
     */
    public function editAction()
    {
        $type = $this->params()->fromRoute('type');
        $date_string = $this->params()->fromRoute('date');
        $id = $this->params()->fromRoute('id');
        if ($this->getRequest()->isGet()) {
            $method = 'get'.\strtoupper($type);
            $note = $this->notesService->$method($id);
            // find the entity to populate the form
            if (! $note && $date_string) {
                return $this->redirect()->toRoute('notes/create',['type'=>$type,'date'=>$date_string]);
            }
            $data = ['date'=> $note->getDate(), 'action'=>'edit','type'=>$type, 'note'=>$note,
                'csrf' => (new \Laminas\Validator\Csrf('csrf'))->getHash()
            ];
            $view = new ViewModel($data);
            if ($this->getRequest()->isXMLHttpRequest()) {
                $view->setTerminal(true)->setTemplate('notes/partials/form');
            } else {
                // inject the other note into the view
                $other_type = $type == 'motd' ? 'motw':'motd';
                $other_note = $this->notesService->getNoteByDate(new \DateTime($date_string),$other_type,true);
                $view->notes = [$other_type => $other_note];
            }
            return $view;
        } else {
            // should not happen. we are for GET requests only.
            return false;
        }
    }

    public function createAction()
    {
        $type = $this->params()->fromRoute('type');
        $date_string = $this->params()->fromRoute('date');
        // make sure someone didn't already create one
        $existing = $this->notesService->getNoteByDate(new \DateTime($date_string),$type);
        if ($existing) {
            return $this->redirect()->toRoute('notes/edit',['type'=>$type,'id'=>$existing->getId(),  'date'=>$date_string]);
        }
        $view = new ViewModel(['date'=>new \DateTime($date_string),'type'=>$type,
        'csrf' => (new \Laminas\Validator\Csrf('csrf'))->getHash()]);
        if ($this->getRequest()->isGet()) {
            if ($this->getRequest()->isXMLHttpRequest()) {
                $view->setTerminal(true)->setTemplate('notes/partials/form');
            }
            return $view;
        }
    }
}
