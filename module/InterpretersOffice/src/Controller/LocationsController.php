<?php

/**
 * module/InterpretersOffice/src/Controller/Factory/LocationsControllerFactory.php
 */

namespace InterpretersOffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
//use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
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
    
    public function indexAction()
    {
        
        return new ViewModel();
    }
    
    public function getChildrenAction()
    {
        $parent_id = $this->params()->fromQuery('parent_id');
        if (! $parent_id) {
            throw new \RuntimeException("missing required parent_id parameter");
        }
        $repo = $this->em->getRepository('InterpretersOffice\Entity\Location');
        // etc
        return new JsonModel(['result'=>'life is good']);
    }
}
