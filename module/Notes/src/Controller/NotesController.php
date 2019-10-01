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

    public function get($id)
    {
        $type = $this->params()->fromRoute('type');
        $id = $this->params()->fromRoute('id');
        return new JsonModel(
            [  'date'=> 'whenever','message'=> __METHOD__, 'id'=>$id,'type'=>$type  ]
        );
    }

    /**
     * still scribbling
     *
     * @return JsonModel
     */
    public function getByDateAction()
    {
        $date =  $this->params()->fromRoute('date');
        $type =  strtoupper($this->params()->fromRoute('type'));

        $class = $type == 'MOTD' ? MOTD::class : MOTW::class;
        $motd = $this->em->getRepository($class)->findByDate(new \DateTime($date));
        $motd->setContent((new Parsedown())->text(nl2br($motd->getContent())));

        return new ViewModel(['motd'=>$motd]);

    }
}
