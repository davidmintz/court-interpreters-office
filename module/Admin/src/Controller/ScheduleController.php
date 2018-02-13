<?php
/**
 * module/Admin/src/Controller/ScheduleController.php
 */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Doctrine\ORM\EntityManagerInterface;
use Zend\Authentication\AuthenticationServiceInterface;

use Zend\EventManager\Event;

use InterpretersOffice\Admin\Form;

use InterpretersOffice\Entity;

/**
 * ScheduleController
 *
 */
class ScheduleController extends AbstractActionController
{
    /**
     * entityManager
     * @var EntityManagerInterface
     */
    protected $entityManager;
    
    /**
     * constructor
     * 
     * @param EntityManagerInterface $e
     */
    public function __construct(EntityManagerInterface $e)
    {
        $this->entityManager = $e;
    }

    /**
     * index action
     * 
     * @return mixed
     */
    public function indexAction()
    {
        $params = $this->params()->fromRoute();
        if (! isset($params['year'])) {
            $date = (new \DateTime())->format('Y-m-d');
        } else {
            $date = sprintf('%s-%s-%s',$params['year'],$params['month'],$params['date']);
        }
        $repo = $this->entityManager->getRepository(Entity\Event::class);
        $data = $repo->getSchedule(['date'=>$date]);
        
        return ['data' => $data];

    }
}
