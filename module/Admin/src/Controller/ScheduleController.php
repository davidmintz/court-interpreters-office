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
use InterpretersOffice\Entity\Event;

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

    /**
     * displays event details
     *
     */
     public function viewAction()
     {
         $id = $this->params()->fromRoute('id');
         $event = $this->entityManager->getRepository(Entity\Event::class)
            ->getView($id);

         return compact('event','id');
     }
    /**
     * computes and sets the "next" and "previous" dates
     *
     * @param ViewModel $view
     * @param \DateTime  $date
     * @return ViewModel
     */

    public function setPreviousAndNext(ViewModel $view, \DateTime $date)
    {
        $string = $date->format('Y-m-d');
        $next = '+1';
        $prev = '-1';
        switch ($date->format('w')) {
            case 0:
                $prev = '-2';
                break;
            case 1:
                $prev = '-3';
                break;
            case 5:
                $next = '+3';
                break;
            case 6:
                $prev = '-2';
                break;
        }
        $view->prev = (new \DateTime("$string $prev days"))->format('/Y/m/d');
        $view->next = (new \DateTime("$string $next days"))->format('/Y/m/d');

        return $view;
    }
}
