<?php

/** module/Admin/src/Controller/DocketAnnotationsController.php */

namespace InterpretersOffice\Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\Validator\Csrf;
use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Entity;

use InterpretersOffice\Admin\Service\DocketAnnotationService;

/**
 * controller for listing and displaying forms for DocketAnnotation entities
 */
class DocketAnnotationsController extends AbstractActionController
{

    /**
     * annotation service
     *
     * @var DocketAnnotationService
     */
    private $service;

    /**
     * constructor
     *
     * @param DocketAnnotationService $service
     */
    public function __construct(DocketAnnotationService $service)
    {
        $this->service = $service;
    }

    /**
     * index
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $docket = $this->params()->fromRoute('docket');
        if ($docket) {
            $data = $this->service->getAnnotations($docket);
        }
        $request = $this->getRequest();
        $view = new ViewModel([
            'docket' => $docket, 'data' => $data ?? false,
            'csrf' => (new Csrf(['timeout' => 1200]))->getHash(),
            'referrer' => $request->getServer()->get('HTTP_REFERER'),
        ]);
        if ($request->isXmlHttpRequest()) {
            $view->setTemplate('docket-annotations/partials/table')
                ->setTerminal(true);
        }

        return $view;
    }

    /**
     * displays edit form
     *
     * @return ViewModel
     */
    public function editAction()
    {

        $id = $this->params()->fromRoute('id');
        $note = $this->service->get((int)$id);
        $token = (new Csrf(['timeout' => 1200]))->getHash();
        return ['note' => $note, 'csrf' => $token];
    }

    /**
     * displays create form
     *
     * @return ViewModel
     */
    public function addAction()
    {
        $token = (new Csrf(['timeout' => 1200]))->getHash();
        return ['csrf' => $token,'docket' => $this->params()->fromRoute('docket')];
    }
}
