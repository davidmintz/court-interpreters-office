<?php
/**
 * module/Application/src/Controller/LanguagesController.php
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Interop\Container\ContainerInterface;

/**
 *  IndexController
 * 
 *  Currently, just for making sure the application runs, basic routing is 
 *  happening, service container is working, views are rendered, etc.
 */

class LanguagesController extends AbstractActionController
{
    /** 
     * service manager.
     * 
     * @var ContainerInterface 
     */
    protected $serviceManager;
    
    /**
     * constructor
     * 
     * @see Application\Controller\Factory\SimpleEntityControllerFactory
     * @param ContainerInterface $serviceManager
     */
    public function __construct(ContainerInterface $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        
    }
    /**
     * index action
     * 
     * @return ViewModel
     */
    public function indexAction()
    {
        
    }
    public function createAction() {
        echo "Hi!";
        return false;
    }
}
