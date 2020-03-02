<?php

/** module/Admin/src/Controller/DocketAnnotationsController.php */

namespace InterpretersOffice\Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Laminas\Authentication\AuthenticationServiceInterface;

use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Entity;

use InterpretersOffice\Admin\Service\DocketAnnotationService;

class DocketAnnotationsController extends AbstractActionController
{

    /**
     * annotation service
     * @var DocketAnnotationService
     */
    private $service;

    public function __construct(DocketAnnotationService $service)
    {
        $this->service = $service;
    }

    public function indexAction()
    {
        $docket = $this->params()->fromRoute('docket');
        if ($docket) {
            $data = $this->service->getAnnotations($docket);
        }
        $view = new ViewModel(['docket'=>$docket, 'data'=>$data ?? false]);
        if ($this->getRequest()->isXmlHttpRequest()) {
            $view->setTemplate('docket-annotations/partials/table')
                ->setTerminal(true);
        }
        
        return $view;
    }

    public function queryAction()
    {
        $docket = $this->params()->fromRoute('docket');
    }


    public function editAction(){

        return false;

    }
    public function deleteAction(){

        return false;
    }
    public function addAction(){

        return false;
    }
}
