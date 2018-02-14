<?php
/**
 * module/Admin/src/Controller/ScheduleController.php
 */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
//use Zend\View\Model\JsonModel;
use Doctrine\ORM\EntityManagerInterface;
//use Zend\Authentication\AuthenticationServiceInterface;

//use Zend\EventManager\Event;

//use InterpretersOffice\Admin\Form;

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
            $date = new \DateTime();
        } else {
            // @todo try/catch here?
            $date = new \DateTime(sprintf('%s-%s-%s',$params['year'],$params['month'],$params['date']));
        }
        $repo = $this->entityManager->getRepository(Entity\Event::class);
        $data = $repo->getSchedule(['date'=>$date->format('Y-m-d')]);
        $day_of_week = $date->format('w');
        
        $viewModel = new ViewModel(['data' => $data, 'date'=>$date]);
        $this->setPreviousAndNext($viewModel, $date);

        return $viewModel;
        
    }
    
    public function setPreviousAndNext(ViewModel $view, \DateTime $date)
    {
        $string = $date->format('Y-m-d');
        switch ($date->format('w')) {
            case 0:
                $next = '+1';
                $prev = '-2';
                break;
            case 1:
                $next = '+1';
                $prev = '-3';
                break;
            case 5:
                $next = '+3';
                $prev = '-1';
                break;
            case 6:
                $next = '+1';                
                $prev = '-2';
                break;
            default:
                $next = '+1';
                $prev = '-1';                
        }
        $view->prev = (new \DateTime("$string $prev days"))->format('/Y/m/d');
        $view->next = (new \DateTime("$string $next days"))->format('/Y/m/d');
        
        return $view;
    }
}
