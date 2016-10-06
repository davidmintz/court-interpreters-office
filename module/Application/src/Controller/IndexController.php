<?php
/**
 * 
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
//use Zend\ServiceManager\ServiceLocatorInterface;
use Interop\Container\ContainerInterface;

class IndexController extends AbstractActionController
{
     /** @var Zend\ServiceManager\ServiceLocatorInterface */
    protected $serviceManager;
    
    public function __construct(ContainerInterface $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function indexAction()
    {
        
        return new ViewModel(['entityManager'=>$this->serviceManager->get('entity-manager')]);
    }

}
