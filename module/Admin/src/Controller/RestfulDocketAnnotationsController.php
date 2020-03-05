<?php

/** module/Admin/src/Controller/DocketAnnotationsController.php */

namespace InterpretersOffice\Admin\Controller;

use Laminas\Mvc\Controller\AbstractRestfulController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\Validator\Csrf;
use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Entity;

use InterpretersOffice\Admin\Service\DocketAnnotationService;

class RestfulDocketAnnotationsController extends AbstractRestfulController
{

    /**
     * annotation service
     * @var DocketAnnotationService
     */
    private $service;

    public function testAction()
    {
        return new JsonModel(['status'=>'test is working']);
    }

    /**
     * constructor
     *
     * @param DocketAnnotationService $service
     */
    public function __construct(DocketAnnotationService $service)
    {
        $this->service = $service;
    }
    public function delete($id)
    {
        return new JsonModel(['status'=>'boink! DELETE']);
    }

    public function update($id, $data) {
        return new JsonModel(['status'=>'boink! UPDATE']);
    }

    public function create($data)
    {
        return new JsonModel($this->service->create($data));
    }
}