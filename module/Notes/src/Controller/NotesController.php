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
     * work in progress. needs to handle not-found, handle different
     * entity-types etc
     *
     * @param  $id  entity id
     * @param  array $data
     *
     * @return JsonModel
     */
    public function update($id, $data)
    {
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
        return new JsonModel(['test'=>'1 2 3']);
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

    public function editAction()
    {

    }

    public function createAction()
    {

    }
}
