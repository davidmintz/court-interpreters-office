<?php

/** module/Admin/src/Controller/DocketAnnotationsController.php */

namespace InterpretersOffice\Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;

use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Entity;

use InterpretersOffice\Admin\Service\DocketAnnotationService;

class DocketAnnotationsController extends AbstractActionController
{
    public function __construct(DocketAnnotationService $service)
    {

    }

    public function indexAction()
    {

    }
}
