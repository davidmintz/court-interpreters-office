<?php /** module/Notes/src/Controller/NotesController.php */
namespace InterpretersOffice\Admin\Notes\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Doctrine\ORM\EntityManagerInterface;
use Zend\View\Model\JsonModel;

use InterpretersOffice\Admin\Notes\Entity\MOTD;

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

    public function __getList()
    {
        return [];
    }

    public function testAction()
    {
        //$type = $this->params()->fromRoute('type');
        return new JsonModel(['date'=> 'whenever','message'=> 'fuck yeah!', ]);
    }

    public function getByDateAction()
    {
        $date =  $this->params()->fromRoute('date');
        $type = $this->params()->fromRoute('type');
        //
        return new JsonModel(['message'=> "fuck yeah! you want $type for $date", 'action'=>__FUNCTION__,'date'=>$date,]);
    }
}
