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
        // temporary, for playing around
        return ['service'=>$this->service];
    }
}
