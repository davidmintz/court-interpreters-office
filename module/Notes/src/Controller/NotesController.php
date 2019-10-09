<?php /** module/Notes/src/Controller/NotesController.php */
namespace InterpretersOffice\Admin\Notes\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
//use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Admin\Notes\Service\NotesService;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
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
     * work in progress.
     *
     * @param  $id  entity id
     * @param  array $data
     *
     * @return JsonModel
     */
    public function update($id, $data)
    {
        $service = $this->notesService;
        $inputFilter = $service->getInputFilter();
        $inputFilter->setData($data);
        if (! $inputFilter->isValid()) {
            $errors = $inputFilter->getMessages();
            return new JsonModel([
                'validation_errors' => $errors,
            ]);
        }
        $type =  $this->params()->fromRoute('type');

        return new JsonModel(
            $service->update($type, (int)$id, $inputFilter->getValues())
        );

        // $note = $service->{'get'.\strtoupper($type)}($id)
        //     ->setContent($inputFilter->get('content')->getValue());
        // $type =  strtoupper($this->params()->fromRoute('type'));
        // $repository = $this->em->getRepository(MOTD::class);
        // $entity = $repository->find($id);
        // $before = $entity->getContent();
        // $mod_before_by = $entity->getModifiedBy()->getId();
        // $entity->setContent($data['content']);
        // if ($before != $data['content']) {
        //     $entity->setModified(new \DateTime());
        //     if ($this->user->id != $mod_before_by) {
        //         $user_entity = $this->em->getRepository(User::class)
        //             ->getUser($this->user->id);
        //         $entity->setModifiedBy($user_entity);
        //     }
        // }
        // $this->em->flush();
        //return new JsonModel([$type=>$entity]);
    }

    public function create($data)
    {

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
            if ($message) {
                $message->setContent((new Parsedown())->text(nl2br($message->getContent())));
            }
            return new $view_class([$type => $message]);
        } else {
            $messages = $service->findAllForDate($date_obj);

            return new $view_class($messages);
        }
    }

    public function updateSettingsAction()
    {
        $params = $this->params()->fromPost();
        $this->notesService->updateSettings($params);

        return new JsonModel($this->notesService->getSession()->settings);
    }

    public function indexAction()
    {

    }

    /**
     * gets MOTD or MOTW by date
     *
     * // to be continued
     *
     * @return JsonModel|ViewModel
     */
    public function getByIdAction()
    {
        $id = $this->params()->fromRoute('id');
        $type =  strtoupper($this->params()->fromRoute('type'));
        $class = $type == 'MOTD' ? MOTD::class : MOTW::class;
        $view_class = $this->getRequest()->isXMLHttpRequest() ?
            JsonModel::class : ViewModel::class;

        return new $view_class(['motd'=>'boink']);
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
            // find the entity
            if (! $note && $date_string) {
                if ($date_string) {
                    return $this->redirect()->toRoute('notes/create',['type'=>$type,'date'=>$date_string]);
                } else {
                    // ?
                }
            }
            return ['date'=> $note->getDate(),'type'=>$type, 'note'=>$note];
        }
    }

    public function createAction()
    {
        $type = $this->params()->fromRoute('type');
        $date_string = $this->params()->fromRoute('date');
        if ($this->getRequest()->isGet()) {
            // to do: first see if one already exists

            return ['date'=>new \DateTime($date_string),'type'=>$type];
        }
        $inputFilter = $this->notesService->getInputFilter();
        // $inputFilter->setData(['content'=>'']);
        // if (! $inputFilter->isValid()) {
        //     print_r($inputFilter->getMessages());
        // }

    }
}
