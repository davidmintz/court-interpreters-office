<?php

/** module/Admin/src/Controller/CourtClosingsController */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Entity;


/**
 * controller for admin/court-closings
 */
class CourtClosingsController extends AbstractActionController
{

    /**
     * entity manager
     *
     * @var EntityManagerInterface
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->objectManager  = $em;
    }
    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $repo = $this->objectManager->getRepository(Entity\CourtClosing::class);
        $data = $repo->list();

        return new ViewModel(['data'=>$data]);
    }

    /**
     * adds a court closing
     */
    public function addAction()
    {
        $view = new ViewModel();
        $view->setTemplate('interpreters-office/admin/court-closings/form');

        return $view;
    }

    /**
     * edits a court closing
     */
    public function editAction()
    {
        $view = new ViewModel();
        $view->setTemplate('interpreters-office/admin/court-closings/form');

        return $view;
    }
}
