<?php

/**
 * module/InterpretersOffice/src/Controller/Factory/LocationsControllerFactory.php
 */

namespace InterpretersOffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\EntityManager;

/**
 * for fetching data to populate dropdown menus
 */
class LocationsController extends AbstractActionController {
    
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
    public function __construct(EntityManager $em) {
        $this->em = $em;
    }
    
    public function indexAction() {
        
        return new ViewModel();
    }
    
}
