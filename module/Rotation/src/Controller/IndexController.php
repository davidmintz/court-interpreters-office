<?php

namespace InterpretersOffice\Admin\Rotation\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\EntityManager;


class IndexController extends AbstractActionController
{

    /**
     * entity manager
     *
     * @var EntityManager
     */
    protected $em;

    /**
     * constructor
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function indexAction()
    {

    }
}
