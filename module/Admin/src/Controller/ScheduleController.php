<?php
/**
 * module/Admin/src/Controller/ScheduleController.php
 */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\EntityManagerInterface;
//use Zend\View\Model\JsonModel;
//use Zend\Authentication\AuthenticationServiceInterface;
//use Zend\EventManager\Event;
//use InterpretersOffice\Admin\Form;

use InterpretersOffice\Entity;
use InterpretersOffice\Entity\Event;
use Zend\Session\Container as Session;

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
     * session
     *
     * @var Zend\Session\Container
     */
    protected $session;

    /**
     * constructor
     *
     * @param EntityManagerInterface $e
     */
    public function __construct(EntityManagerInterface $e)
    {
        $this->entityManager = $e;
        $this->session = new Session('schedule');
    }

    /**
     * index action
     *
     * @return mixed
     */
    public function scheduleAction()
    {

        $filters = $this->getFilters();
        $date = new \DateTime($filters['date']);
        $repo = $this->entityManager->getRepository(Entity\Event::class);
        $data = $repo->getSchedule($filters);
        $viewModel = new ViewModel(compact('data', 'date'));
        $this->setPreviousAndNext($viewModel, $date)
            ->setVariable('language', $filters['language']);
        if ($this->getRequest()->isXmlHttpRequest()) {
            $viewModel
                ->setTemplate('interpreters-office/admin/schedule/partials/table')
                ->setTerminal(true);
        }
        return $viewModel;
    }
    /**
     * gets date and language filters for schedule
     *
     * look first to GET parameters, then the session, otherwise
     * default to today's date and null language filter
     *
     * @return Array
     */
    public function getFilters()
    {
        $date_params = [];
        $params = $this->params()->fromRoute();
        foreach (['year','month','date'] as $p) {
            if (isset($params[$p])) {
                $date_params[$p] = $params[$p];
            }
        }
        if ($date_params) {
            $date = implode('-', $date_params);
            $this->session->date = $date;
        } elseif ($this->session->date) {
            $date = $this->session->date;
        } else { // default to today
            $date = date('Y-m-d');
        }
        $language = strtolower($this->params()->fromQuery('language'));
        if (! in_array($language, ['spanish','not-spanish','all'])) {
            $language = null;
        }
        if ($language) {
            $this->session->language = $language;
        } elseif ($this->session->language) {
            $language = $this->session->language;
        } else {
            $language = 'all';
        }

        return [ 'date' => $date, 'language' => $language];
    }

    /**
     * displays event details
     */
    public function viewAction()
    {
        $id = $this->params()->fromRoute('id');
        $event = $this->entityManager->getRepository(Entity\Event::class)
           ->getView($id);
        $csrf = (new \Zend\Validator\Csrf('csrf'))->getHash();

        return compact('event', 'id', 'csrf');
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
