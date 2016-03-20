<?php
/**
 * main controller
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\ServiceManager\ServiceLocatorInterface;

class IndexController extends AbstractActionController
{
    /** @var Zend\ServiceManager\ServiceLocatorInterface */
    protected $serviceManager;
    
    public function __construct(ServiceLocatorInterface $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function indexAction()
    {
        
    	//$em = $this->serviceManager->get('doctrine.entitymanager.orm_default');
    	//printf("Good news from %s:  we have a %s at line %d<br>",__CLASS__,get_class($em),__LINE__);
    	//printf("...and %s<br>",get_class($em->getConnection()));
        return new ViewModel();
    }
}
