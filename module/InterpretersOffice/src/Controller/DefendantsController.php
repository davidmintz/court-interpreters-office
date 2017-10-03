<?php
/**
 * module/InterpretersOffice/src/Controller/DefendantsController.php
 */

namespace InterpretersOffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;       
use Zend\View\Model\JsonModel;
use Doctrine\ORM\EntityManager;

use InterpretersOffice\Entity;

/**
 *
 * for fetching defendant data for autocompletion, etc
 */
class DefendantsController extends AbstractActionController {
    
    /**
     * entity manager
     * 
     * @var EntityManager
     */
    protected $entityManager;
    
    /**
     * constructor
     * 
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em) {
        $this->entityManager = $em;
    }
    
    /**
     * don't really need this. it can be removed
     * 
     * @return \InterpretersOffice\Controller\ViewModel
     */
    public function indexAction()
    {        
        return $this->getResponse()
            ->setContent("LocationsController/indexAction works");
    }
    
   /**
     * autocompletion for the defendant-name search box in 
     * the interpreter-request form
     */
    public function autocompleteAction()
    {
        $repo = $this->entityManager->getRepository(Entity\DefendantName::class);
        $term = $this->params()->fromQuery('term');
        $data = $repo->autocomplete($term);
  
        return new JsonModel($data);       
    }

}
