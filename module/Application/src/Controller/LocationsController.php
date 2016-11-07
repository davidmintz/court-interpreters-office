<?php
/**
 * module/Application/src/Controller/IndexController.php
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Interop\Container\ContainerInterface;

/**
 *  LocationsController
 * 
 * For managing the locations to which interpreters are deployed
  *  
 */

class LocationsController extends AbstractActionController
{
    
    use AnnotatedFormCreationTrait;
    
    /** 
     * FormElementManager
     * 
     * for instantiating the Form
     * 
     * @var AbstractPluginManager
     */
    protected $formElementManager;
    
    /**
     * entity manager
     * 
     * @var EntityManagerInterface
     */
    protected $entityManager;
    
    /**
     * short name of this controller
     * @var string 
     */
    protected $name;
    
    /**
     * constructor
     * 
     * @param EntityManagerInterface $entityManager
     * @param AbstractPluginManager $formElementManager 
     * @param string $shortName this controller's short name/type of entity
     * 
     * @see Application\Controller\Factory\SimpleEntityControllerFactory
     * 
     */
    public function __construct(EntityManagerInterface $entityManager, 
            AbstractPluginManager $formElementManager, $shortName)
    {
        $this->entityManager = $entityManager;
        $this->formElementManager = $formElementManager;
        $this->name = $shortName;
        
    }
    /**
     * index action
     * 
     * @return ViewModel
     */
    public function indexAction()
    {
        echo "hurray for indexAction."; return false;
    }
     /**
     * adds a new Location
     * 
     * @return ViewModel
     */
    public function addAction()
    {
        echo "hurray for addAction."; return false;
    }
    /**
     * edits a Location
     * 
     * @return ViewModel
     */
    public function editAction()
    {
        echo "hurray for editAction!"; return false;
    }
    
}
