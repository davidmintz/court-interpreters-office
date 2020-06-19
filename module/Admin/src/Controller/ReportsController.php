<?php /** module/Admin/src/Controller/ReportsController.php  */

namespace InterpretersOffice\Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
// use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Admin\Service\ReportService;
// use InterpretersOffice\Entity;

/**
 * reports controller
 *
 * a work in progress
 */
class ReportsController extends AbstractActionController
{

     /**
     * entity manager
     *
     * @var ReportService
     */
    private $reports;

    /**
     * @var SessionContainer
     *
     * session
     */
    private $session;


    /**
     * constructor
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(ReportService $reports)
    {
        $this->reports = $reports;
        $this->session = new \Laminas\Session\Container("reports");
    }

    /**
     * index page
     */
    public function indexAction()
    {        
        $params = $this->params()->fromQuery();
        if ($params) {
           
            $input = $this->reports->getInputFilter();
            $input->setData($params);
            if (! $input->isValid()) {
                return new JsonModel(['validation_errors'=>$input->getMessages()]);
            }
            return new JsonModel(['data'=>['boink!']]);
        }
        return ['reports'=>$this->reports->getReports(),
            'defaults'=>$this->getDefaults()];
    }
    /**
     * figures out default report settings
     * 
     * @return array
     */
    private function getDefaults()
    {
        
        $get = $this->params()->fromQuery();
        if ($get) {
            $filter = $this->service->getInputFilter();
            $filter->setData($get);
        }
        
        return [];
    }
}
