<?php /** module/Notes/src/Controller/NotesController.php */

namespace InterpretersOffice\Admin\Notes\Controller;

use Laminas\Mvc\Controller\AbstractRestfulController;
use InterpretersOffice\Admin\Notes\Service\NotesService;
use InterpretersOffice\Admin\Notes\Entity;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use DateTime;

/**
 * controller for Notes (MOT[DW]) entities
 */
class NotesController extends AbstractRestfulController
{
  
    /**
     *
     * @var stdClass $user
     * 
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
     * @param NotesService $notesService
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
     * edits multiple Notes at once
     * 
     * @param  Array  $data
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
     * @param string $date
     * @return JsonModel
     */
    public function get($date)
    {
        $xhr = $this->getRequest()->isXMLHttpRequest();
        if (! $xhr) { return $this->getList();}
        $view = new JsonModel();
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
            $view->setVariables([$type => $message]);
        } else {
            $messages = $service->getAllForDate($date_obj);
            $view->setVariables($messages);
        }
        // if ($view_class == JsonModel::class) {
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
        // }

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
        // print_r($_SESSION['notes']);
        // $log = $this->getEvent()->getApplication()->getServiceManager()->get('log');
        // $route = $this->getEvent()->getRouteMatch()->getMatchedRouteName();
        // $session = new \Laminas\Session\Container('notes')
        $params =$this->params()->fromRoute();
        if (isset($params['id']) && preg_match('/\d{4}-\d{2}-\d{2}/',$params['id'])) {
            $date_string = $params['id'];
        } else {
            $session = new \Laminas\Session\Container('notes');
            $date_string = $this->params()->fromRoute('date',$session->settings['date']) ?? date('Y-m-d');
        }
        $date = new DateTime($date_string);
        $view = new ViewModel();
        $view->date = new DateTime($date_string);
        $monday = $this->notesService->normalize($date);
        $notes = $this->notesService->getAllForDate($date);
        
        return $view->setVariables(['notes' => $notes,'action'=>'index','monday'=>$monday,]);
    }

    /**
     * renders form for MOTD
     *
     */
    public function editAction()
    {
        $type = $this->params()->fromRoute('type');        
        $date_string = $this->params()->fromRoute('date');
        if ('motw' == $type) {
            //$date_string = $this->notesService->getSession()['settings']['date'];
            $schedule = new \Laminas\Session\Container('schedule');
            $motd_date = new \DateTime($schedule->date ?: $date_string);
            $monday = new \DateTime($date_string);
        } else {
            $motd_date = new \DateTime($date_string);            
            $monday = $this->notesService->normalize($motd_date);            
        }
        $id = $this->params()->fromRoute('id');
        if ($this->getRequest()->isGet()) {
            $method = 'get'.\strtoupper($type);
            $note = $this->notesService->$method($id);
            // find the entity to populate the form
            if (! $note && $date_string) {
                return $this->redirect()->toRoute('notes/create',['type'=>$type,'date'=>$date_string]);
            }            
            $data = [
                //'date'=> $date, 
                'monday' =>  $monday,'date'=>$motd_date,
                'action'=>'edit','type'=>$type,'note'=>$note,
                'csrf' => (new \Laminas\Validator\Csrf('csrf'))->getHash()
            ];
            $view = new ViewModel($data);
            if ($this->getRequest()->isXMLHttpRequest()) {
                $view->setTerminal(true)->setTemplate('notes/partials/form');
            } else {
                // inject the other note into the view
                if ($type == 'motd') {
                    // the other is motw
                    $other_type = 'motw';
                    $string = $monday->format('Y-m-d');
                } else {
                    $other_type = 'motd';
                    $string = $motd_date->format('Y-m-d');
                }                
                // echo "fetching $other_type for $string ???";
                $other_note = $this->notesService->getNoteByDate(new \DateTime($string),$other_type,true);
                $view->notes = [$type => $note, $other_type => $other_note, ];
            }
            return $view;
        } else {
            // should not happen. we are for GET requests only.
            return false;
        }
    }

    /**
     * note creation
     * 
     */
    public function createAction()
    {
        $type = $this->params()->fromRoute('type');
        $date_string = $this->params()->fromRoute('date');
        try {
            $date_obj = new \DateTime($date_string);
        } catch (\Exception $e) {
            // bad date parameter. and the route constraint should prevent this but does not.
            return new ViewModel(['error_message'=>'Invalid url parameter: '.$date_string . '. The format YYYY-MM-DD is required.']);
        }
        if ('motw' == $type) {
            //$date_string = $this->notesService->getSession()['settings']['date'];
        }
        $notes = $this->notesService->getAllForDate($date_obj);
        // make sure someone didn't already create one
        $existing = $notes[$type]; //$this->notesService->getNoteByDate(new \DateTime($date_string),$type);
        if ($existing) {
            return $this->redirect()->toRoute('notes/edit',['type'=>$type,'id'=>$existing->getId(),'date'=>$date_string]);
        }
        // intialize new empty Note
        $class = 'InterpretersOffice\\Admin\\Notes\\Entity\\'.strtoupper($type);
        $note = new $class;
        $date = new \DateTime($date_string);
        $monday =  $this->notesService->normalize($date);
        if ($type == 'motw') { 
            $note->setDate(new \DateTime($monday->format('Y-m-d')));            
        } else {
            $note->setDate($date);
        }        
        $notes[$type] = $note;
        $view = new ViewModel(
            ['date'=>$date,'type'=>$type,
            'notes'=>$notes, // note the plural
            'monday'=>$monday ,
            // 'note'=>$note, // do away with this?
        'csrf' => (new \Laminas\Validator\Csrf('csrf'))->getHash()]);
        if ($this->getRequest()->isGet()) {
            if ($this->getRequest()->isXMLHttpRequest()) {
                $view->setTerminal(true)->setTemplate('notes/partials/form');
            }
            return $view;
        }
    }

    /**
     * deletion
     *
     * @param  int $id
     * @return JsonModel
     */
    public function delete($id)
    {
        $header = $this->getRequest()->getHeaders('X-Security-Token');
        $type = $this->params()->fromRoute('type');

        return new JsonModel($this->notesService->delete($type, $id, $header ? $header->getFieldValue():''));
    }
}
