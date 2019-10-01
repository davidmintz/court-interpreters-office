<?php /** module/Notes/src/Controller/NotesController.php */
namespace InterpretersOffice\Admin\Notes\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Doctrine\ORM\EntityManagerInterface;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

use InterpretersOffice\Admin\Notes\Entity\MOTD;
use InterpretersOffice\Admin\Notes\Entity\MOTW;
use Parsedown;

class NotesController extends AbstractRestfulController
{

    /**
     * entity manager
     *
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * constructor
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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
        $repository = $this->em->getRepository(MOTD::class);
        if ('ALL' != $type) {
            $message = $repository->findByDate(new \DateTime($date),$type);
            $message->setContent((new Parsedown())->text(nl2br($message->getContent())));
            return new $view_class([$type => $message]);
        } else {
            $messages = $repository->findAllForDate(new \DateTime($date));
            return new $view_class($messages);
        }
    }

    /**
     * gets MOTD or MOTW by date
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
}
